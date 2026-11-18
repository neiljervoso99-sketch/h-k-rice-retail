<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderReceiptItem;
use App\Models\Supplier;
use App\Support\LowStockAlertNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier')
            ->withCount('items', 'receipts')
            ->latest()
            ->paginate(20);
        $suppliers = Supplier::query()
            ->withCount('purchaseOrders')
            ->orderBy('name')
            ->paginate(20, ['*'], 'suppliers_page');
        $hasSalesOrders = Order::query()->exists();

        return view(
            'admin.purchase-orders.index',
            compact('purchaseOrders', 'suppliers', 'hasSalesOrders')
        );
    }

    public function destroyAll()
    {
        $result = DB::transaction(function (): array {
            $purchaseOrderIds = PurchaseOrder::query()
                ->lockForUpdate()
                ->get(['id'])
                ->modelKeys();
            $orderIds = Order::query()
                ->lockForUpdate()
                ->get(['id'])
                ->modelKeys();

            if ($purchaseOrderIds === [] && $orderIds === []) {
                return ['deleted_count' => 0, 'proof_paths' => []];
            }

            $orders = Order::query()
                ->whereIn('id', $orderIds)
                ->with('payments:id,order_id,proof_path')
                ->get(['id', 'delivery_proof_path']);
            $proofPaths = $orders
                ->flatMap(fn (Order $order) => [
                    $order->delivery_proof_path,
                    ...$order->payments->pluck('proof_path')->all(),
                ])
                ->filter()
                ->unique()
                ->values()
                ->all();

            $receiptItemIds = PurchaseOrderReceiptItem::query()
                ->whereHas('receipt', fn ($query) => $query->whereIn(
                    'purchase_order_id',
                    $purchaseOrderIds
                ))
                ->pluck('id');

            InventoryMovement::query()
                ->whereIn('purchase_order_receipt_item_id', $receiptItemIds)
                ->update(['purchase_order_receipt_item_id' => null]);

            PurchaseOrderReceipt::query()
                ->whereIn('purchase_order_id', $purchaseOrderIds)
                ->delete();
            PurchaseOrder::query()
                ->whereIn('id', $purchaseOrderIds)
                ->delete();
            ProductReview::query()
                ->whereIn('order_id', $orderIds)
                ->update(['order_id' => null]);
            Order::query()
                ->whereIn('id', $orderIds)
                ->delete();

            return [
                'deleted_count' => count($purchaseOrderIds) + count($orderIds),
                'proof_paths' => $proofPaths,
            ];
        }, 3);

        Storage::disk('local')->delete($result['proof_paths']);

        $message = $result['deleted_count'] > 0
            ? 'Purchase orders, receipts, customer orders, POS sales, and linked payment records were deleted. Inventory, reviews, and cash-register history were kept.'
            : 'There are no transaction records to delete.';

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', $message);
    }

    public function create()
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'admin.purchase-orders.create',
            compact('suppliers', 'products')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'expected_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.ordered_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:1000000',
                'decimal:0,3',
            ],
            'items.*.unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000000',
            ],
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated, $request) {
            $supplier = Supplier::query()
                ->whereKey($validated['supplier_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $supplier->is_active) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Select an active supplier.',
                ]);
            }

            $products = collect($validated['items'])
                ->pluck('product_id')
                ->unique()
                ->sort()
                ->mapWithKeys(fn ($id) => [
                    $id => Product::query()
                        ->whereKey($id)
                        ->lockForUpdate()
                        ->firstOrFail(),
                ]);

            $now = now();
            do {
                $number = 'HK-PO-'.$now->format('Ymd').'-'
                    .Str::upper(Str::random(8));
            } while (PurchaseOrder::where('order_number', $number)->exists());

            $purchaseOrder = PurchaseOrder::create([
                'order_number' => $number,
                'supplier_id' => $supplier->id,
                'status' => 'ordered',
                'expected_at' => $validated['expected_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['items'] as $index => $input) {
                $product = $products->get($input['product_id']);

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" => "The product {$product->name} is inactive.",
                    ]);
                }

                $isGeneric = ($product->inventory_type ?? 'rice') === 'generic';
                $orderedQuantity = round(
                    (float) $input['ordered_quantity'],
                    3
                );

                if (
                    ! $isGeneric
                    && abs($orderedQuantity - round($orderedQuantity, 2)) > 0.000001
                ) {
                    throw ValidationException::withMessages([
                        "items.$index.ordered_quantity" => 'Rice quantities can have at most two decimal places.',
                    ]);
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'ordered_quantity' => $orderedQuantity,
                    'received_quantity' => 0,
                    'unit' => $isGeneric
                        ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
                        : 'kg',
                    'unit_cost' => $input['unit_cost'] ?? null,
                ]);
            }

            return $purchaseOrder;
        }, 3);

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'creator',
            'items.product',
            'receipts.receiver',
            'receipts.items.purchaseOrderItem',
        ]);
        $idempotencyKey = (string) Str::uuid();

        return view(
            'admin.purchase-orders.show',
            compact('purchaseOrder', 'idempotencyKey')
        );
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'received_at' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array'],
            'items.*' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000000',
                'decimal:0,3',
            ],
        ]);

        $result = DB::transaction(function () use (
            $validated,
            $purchaseOrder,
            $request
        ) {
            $lockedOrder = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingReceipt = PurchaseOrderReceipt::query()
                ->where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($existingReceipt) {
                if ($existingReceipt->purchase_order_id !== $lockedOrder->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This receipt token belongs to a different purchase order.',
                    ]);
                }

                return [
                    'receipt' => $existingReceipt,
                    'product_ids' => [],
                ];
            }

            if (! in_array($lockedOrder->status, ['ordered', 'partially_received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'This purchase order is not open for receiving.',
                ]);
            }

            $items = $lockedOrder->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $knownItemIds = $items->modelKeys();

            foreach (array_keys($validated['items']) as $itemId) {
                if (! in_array((int) $itemId, $knownItemIds, true)) {
                    throw ValidationException::withMessages([
                        'items' => 'A received quantity references an item outside this purchase order.',
                    ]);
                }
            }

            $receipt = PurchaseOrderReceipt::create([
                'purchase_order_id' => $lockedOrder->id,
                'idempotency_key' => $validated['idempotency_key'],
                'received_by' => $request->user()->id,
                'received_at' => $validated['received_at'],
                'notes' => $validated['notes'] ?? null,
            ]);
            $productIds = [];

            foreach ($items as $item) {
                $quantity = round(
                    (float) ($validated['items'][$item->id] ?? 0),
                    3
                );

                if ($quantity <= 0) {
                    continue;
                }

                $remaining = round(
                    (float) $item->ordered_quantity
                        - (float) $item->received_quantity,
                    3
                );

                if ($quantity > $remaining) {
                    throw ValidationException::withMessages([
                        "items.{$item->id}" => "Received quantity cannot exceed the remaining {$remaining} {$item->unit} for {$item->product_name}.",
                    ]);
                }

                if ($item->product_id === null) {
                    throw ValidationException::withMessages([
                        'items' => "Product {$item->product_name} no longer exists.",
                    ]);
                }

                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$item->id}" => "The product {$product->name} is inactive.",
                    ]);
                }

                $isGeneric = ($product->inventory_type ?? 'rice') === 'generic';

                if (
                    ! $isGeneric
                    && abs($quantity - round($quantity, 2)) > 0.000001
                ) {
                    throw ValidationException::withMessages([
                        "items.{$item->id}" => 'Rice quantities can have at most two decimal places.',
                    ]);
                }

                if ($isGeneric) {
                    $newStock = round(
                        (float) $product->stock_base_quantity + $quantity,
                        3
                    );

                    if ($newStock > 1000000) {
                        throw ValidationException::withMessages([
                            "items.{$item->id}" => 'This receipt would exceed the maximum allowed stock.',
                        ]);
                    }

                    $product->stock_base_quantity = $newStock;
                    $this->syncLegacyGenericStock($product);
                    $movementQuantityKg = 0;
                } else {
                    $newStock = round(
                        (float) $product->stock_kg + $quantity,
                        2
                    );

                    if ($newStock > 1000000) {
                        throw ValidationException::withMessages([
                            "items.{$item->id}" => 'This receipt would exceed the maximum allowed stock.',
                        ]);
                    }

                    $product->stock_kg = $newStock;
                    $product->save();
                    $movementQuantityKg = $quantity;
                }

                $receiptItem = PurchaseOrderReceiptItem::create([
                    'purchase_order_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item->id,
                    'quantity' => $quantity,
                ]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'product_name' => $item->product_name,
                    'movement_type' => 'stock_in',
                    'quantity_kg' => $movementQuantityKg,
                    'quantity' => $quantity,
                    'unit' => $item->unit,
                    'movement_date' => $validated['received_at'],
                    'notes' => 'Supplier: '.$lockedOrder->supplier->name
                        .'\nPurchase order '.$lockedOrder->order_number
                        .'; receipt '.$receipt->id
                        .(filled($validated['notes'] ?? null)
                            ? "\n".$validated['notes']
                            : ''),
                    'created_by' => $request->user()->id,
                    'purchase_order_receipt_item_id' => $receiptItem->id,
                ]);

                $item->received_quantity = round(
                    (float) $item->received_quantity + $quantity,
                    3
                );
                $item->save();
                $productIds[] = $product->id;
            }

            if (! $productIds) {
                throw ValidationException::withMessages([
                    'items' => 'Enter at least one quantity to receive.',
                ]);
            }

            $itemsComplete = $lockedOrder->items()
                ->get()
                ->every(fn (PurchaseOrderItem $item) => (float) $item->received_quantity
                        >= (float) $item->ordered_quantity
                );
            $lockedOrder->status = $itemsComplete
                ? 'received'
                : 'partially_received';
            $lockedOrder->save();

            return [
                'receipt' => $receipt,
                'product_ids' => array_values(array_unique($productIds)),
            ];
        }, 3);

        foreach ($result['product_ids'] as $productId) {
            $product = Product::find($productId);

            if ($product) {
                LowStockAlertNotifier::check($product);
            }
        }

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with(
                'success',
                'Receipt '.$result['receipt']->id.' recorded successfully.'
            );
    }

    private function syncLegacyGenericStock(Product $product): void
    {
        $baseUnit = strtolower(trim(
            (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
        ));
        $stockUnit = strtolower(trim((string) $product->stock_unit));
        $factor = (float) $product->unit_conversion_factor;

        if ($baseUnit === $stockUnit && abs($factor - 1.0) < 0.000001) {
            $product->stock_quantity = round(
                (float) $product->stock_base_quantity,
                3
            );
        } elseif (is_finite($factor) && $factor > 0) {
            $product->stock_quantity = round(
                (float) $product->stock_base_quantity / $factor,
                3
            );
        }

        $product->save();
    }
}
