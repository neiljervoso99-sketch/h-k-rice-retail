<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\User;
use App\Support\LowStockAlertNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManualTransactionController extends Controller
{
    private function isGeneric(Product $product): bool
    {
        return ($product->inventory_type ?? 'rice') === 'generic';
    }

    public function index()
    {
        $orders = Order::with(['items', 'payments'])
            ->where('delivery_method', 'pickup')
            ->whereNotNull('inventory_deducted_at')
            ->where('admin_notes', 'like', '%Manual transaction%')
            ->latest('completed_at')
            ->paginate(15);

        return view('admin.manual-transactions.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::with('sellingUnits')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productData = $products->map(function ($product) {
            $generic = $this->isGeneric($product);
            $productName = strtolower(trim((string) $product->name));
            $imageMap = [
                'carlos imported 25 kg' => 'carlos imported.jpg',
                'carlos uno imported' => 'carlos uno imported.jpg',
                'carlos uno local' => 'carlos uno local.jpg',
                'don ramon' => 'bon ramon.jpg',
                'jasmine 10 kg' => 'Jasmine 10 kg.jpg',
                'jasmine 5 kg' => 'Jasmine 5 kg.jpg',
                'red rice' => 'Red Rice 50 kg.jpg',
                'senior roberto 25 kg' => 'Senior Roberto 25 kg.jpg',
                'senior roberto 50 kg' => 'Senior Roberto 50 kg.png',
            ];
            $mappedImage = null;

            foreach ($imageMap as $name => $filename) {
                if (str_contains($productName, $name)) {
                    $candidate = public_path('images/products/' . $filename);

                    if (is_file($candidate)) {
                        $mappedImage = asset('images/products/' . rawurlencode($filename));
                    }

                    break;
                }
            }

            $uploadedImage = $product->image
                ? ltrim(str_replace('\\', '/', $product->image), '/')
                : null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'rice_type' => $product->rice_type,
                'image_url' => $uploadedImage
                    && Storage::disk('public')->exists($uploadedImage)
                        ? asset('storage/' . $uploadedImage)
                        : ($mappedImage ?: asset('images/default-product.svg')),
                'inventory_type' => $generic ? 'generic' : 'rice',
                'stock_unit' => $generic
                    ? strtolower(trim((string) (
                        $product->base_unit ?: $product->stock_unit ?: 'piece'
                    )))
                    : 'kg',
                'stock_quantity' => $generic
                    ? (float) ($product->stock_base_quantity ?? 0)
                    : (float) $product->stock_kg,
                'price_per_unit' => $generic
                    ? (float) $product->price_per_unit
                    : null,
                'price_per_kg' => (float) $product->price_per_kg,
                'price_per_sack' => (float) $product->price_per_sack,
                'sack_weight_kg' => (float) $product->sack_weight_kg,
                'stock_kg' => (float) $product->stock_kg,
                'price_confirmed' => (bool) $product->price_confirmed,
                'selling_units' => $generic
                    ? $product->sellingUnits
                        ->where('is_active', true)
                        ->map(fn ($unit) => [
                            'unit_name' => $unit->unit_name,
                            'conversion_factor' => (float) $unit->conversion_factor,
                            'selling_price' => (float) $unit->selling_price,
                            'cost_price' => $unit->cost_price !== null
                                ? (float) $unit->cost_price
                                : null,
                        ])
                        ->values()
                        ->all()
                    : [],
            ];
        })->values();

        return view(
            'admin.manual-transactions.create',
            compact('products', 'productData')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cash,gcash'],
            'gcash_reference_number' => [
                'required_if:payment_method,gcash',
                'nullable',
                'string',
                'max:100',
            ],
            'cash_received' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100000000',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.unit_type' => [
                'required',
                'string',
                'max:20',
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:100000',
                'decimal:0,2',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
                'max:1000000',
            ],
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $walkIn = User::where('email', 'walkin@hk-store.local')
                ->lockForUpdate()
                ->first();

            if (!$walkIn) {
                $walkIn = User::create([
                    'name' => 'Walk-in Customer',
                    'email' => 'walkin@hk-store.local',
                    'password' => Hash::make(Str::random(64)),
                    'role' => 'customer',
                    'is_admin' => false,
                ]);
            } elseif (
                $walkIn->name !== 'Walk-in Customer'
                || $walkIn->is_admin
                || $walkIn->role !== 'customer'
            ) {
                throw ValidationException::withMessages([
                    'customer_name' => 'The internal walk-in account needs administrator review.',
                ]);
            }

            $preparedItems = [];
            $subtotal = 0.0;

            foreach ($validated['items'] as $index => $input) {
                $product = Product::whereKey($input['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        "items.$index.product_id" =>
                            "The product {$product->name} is inactive.",
                    ]);
                }

                $generic = $this->isGeneric($product);
                $quantity = round((float) $input['quantity'], 2);
                $unitType = (string) $input['unit_type'];
                $submittedPrice = round((float) $input['unit_price'], 2);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        "items.$index.quantity" => 'Quantity must be greater than zero.',
                    ]);
                }

                $sellingUnit = null;
                $baseQuantity = null;

                if ($generic) {
                    $unitType = strtolower(trim($unitType));

                    $sellingUnit = ProductSellingUnit::query()
                        ->where('product_id', $product->id)
                        ->whereRaw('LOWER(TRIM(unit_name)) = ?', [$unitType])
                        ->where('is_active', true)
                        ->first();

                    if (!$sellingUnit) {
                        throw ValidationException::withMessages([
                            "items.$index.unit_type" =>
                                "Please select a valid selling unit for {$product->name}.",
                        ]);
                    }

                    $factor = (float) $sellingUnit->conversion_factor;
                    $availableBaseStock = (float) $product->stock_base_quantity;

                    if (!is_finite($factor) || $factor <= 0) {
                        throw ValidationException::withMessages([
                            "items.$index.unit_type" =>
                                "The conversion factor for {$product->name} is invalid.",
                        ]);
                    }

                    if (!is_finite($availableBaseStock) || $availableBaseStock < 0) {
                        throw ValidationException::withMessages([
                            "items.$index.product_id" =>
                                "The recorded base stock for {$product->name} is invalid.",
                        ]);
                    }

                    $baseQuantity = round($quantity * $factor, 3);

                    if (!is_finite($baseQuantity) || $baseQuantity <= 0) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" =>
                                "The converted quantity for {$product->name} is invalid.",
                        ]);
                    }

                    if ($baseQuantity > $availableBaseStock) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" =>
                                "Insufficient stock for {$product->name}. "
                                . "Available: {$availableBaseStock} "
                                . ($product->base_unit ?: 'base units') . ".",
                        ]);
                    }

                    $registeredPrice = (float) $sellingUnit->selling_price;

                    if (
                        abs($submittedPrice - $registeredPrice) > 0.009
                    ) {
                        throw ValidationException::withMessages([
                            "items.$index.unit_price" =>
                                "The submitted price for {$product->name} "
                                . "does not match its registered selling-unit price.",
                        ]);
                    }

                    $sackWeight = null;
                    $quantityKg = 0;
                    $unitCost = $sellingUnit->cost_price;
                    $movementUnit = strtolower(trim(
                        (string) ($product->base_unit ?: 'piece')
                    ));
                } else {
                    if (!in_array($unitType, ['kg', 'sack'], true)) {
                        throw ValidationException::withMessages([
                            "items.$index.unit_type" =>
                                'Rice can only be sold by kilogram or sack.',
                        ]);
                    }

                    $sackWeight = (int) $product->sack_weight_kg;

                    if ($unitType === 'sack' && $sackWeight < 5) {
                        throw ValidationException::withMessages([
                            "items.$index.unit_type" =>
                                "The sack weight for {$product->name} is invalid.",
                        ]);
                    }

                    $quantityKg = round(
                        $unitType === 'sack'
                            ? $quantity * $sackWeight
                            : $quantity,
                        2
                    );

                    $availableStock = (float) $product->stock_kg;

                    if ($quantityKg > $availableStock) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" =>
                                "Insufficient stock for {$product->name}. Available: {$availableStock} kg.",
                        ]);
                    }

                    $unitCost = null;

                    if (
                        $product->cost_per_sack !== null
                        && $sackWeight > 0
                    ) {
                        $unitCost = $unitType === 'sack'
                            ? round((float) $product->cost_per_sack, 2)
                            : round(
                                (float) $product->cost_per_sack / $sackWeight,
                                2
                            );
                    }

                    $movementUnit = 'kg';
                }

                if (
                    $product->price_confirmed
                    && !$generic
                ) {
                    $registeredPrice = $unitType === 'sack'
                        ? $product->price_per_sack
                        : $product->price_per_kg;

                    if (
                        $registeredPrice !== null
                        && abs($submittedPrice - (float) $registeredPrice) > 0.009
                    ) {
                        throw ValidationException::withMessages([
                            "items.$index.unit_price" =>
                                "The submitted price for {$product->name} does not match its registered price.",
                        ]);
                    }
                }

                $lineTotal = round($quantity * $submittedPrice, 2);
                $subtotal = round($subtotal + $lineTotal, 2);

                $preparedItems[] = [
                    'product' => $product,
                    'is_generic' => $generic,
                    'quantity' => $quantity,
                    'unit_type' => $unitType,
                    'base_quantity' => $baseQuantity,
                    'sack_weight_kg' => $sackWeight,
                    'quantity_kg' => $quantityKg,
                    'unit_price' => $submittedPrice,
                    'line_total' => $lineTotal,
                    'unit_cost' => $unitCost,
                    'movement_unit' => $movementUnit,
                ];
            }

            if ($validated['payment_method'] === 'cash') {
                $cashInput = $validated['cash_received'] ?? null;

                if ($cashInput === null || !is_numeric($cashInput)) {
                    throw ValidationException::withMessages([
                        'cash_received' => 'Please enter the cash received.',
                    ]);
                }

                if (round((float) $cashInput, 2) < $subtotal) {
                    throw ValidationException::withMessages([
                        'cash_received' =>
                            'Cash received must be at least the transaction total.',
                    ]);
                }
            }

            $now = now();

            do {
                $orderNumber = 'HK-' . $now->format('Ymd') . '-'
                    . Str::upper(Str::random(10));
            } while (Order::where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $walkIn->id,
                'recipient_name' => $validated['customer_name']
                    ?? 'Walk-in Customer',
                'phone' => !empty($validated['customer_phone'])
                    ? $validated['customer_phone']
                    : 'N/A',
                'address_line' => null,
                'barangay' => null,
                'delivery_method' => 'pickup',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'status' => 'completed',
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'total' => $subtotal,
                'receipt_requested' => false,
                'cash_collected_amount' => $validated['payment_method'] === 'cash'
                    ? round((float) $validated['cash_received'], 2)
                    : null,
                'cash_collected_at' => $validated['payment_method'] === 'cash'
                    ? $now
                    : null,
                'customer_notes' => $validated['notes'] ?? null,
                'admin_notes' => 'Manual transaction recorded by admin.',
                'accepted_at' => $now,
                'completed_at' => $now,
                'processing_at' => $now,
                'delivered_at' => $now,
                'inventory_deducted_at' => $now,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'method' => $validated['payment_method'],
                'amount' => $subtotal,
                'status' => 'verified',
                'reference_number' =>
                    $validated['payment_method'] === 'gcash'
                        ? trim($validated['gcash_reference_number'])
                        : null,
                'verified_by' => $request->user()->id,
                'verified_at' => $now,
                'notes' => 'Manual transaction recorded by admin.',
            ]);

            foreach ($preparedItems as $item) {
                $product = $item['product'];

                if ($item['is_generic']) {
                    $product->stock_base_quantity = round(
                        (float) $product->stock_base_quantity
                            - (float) $item['base_quantity'],
                        3
                    );

                    $baseUnit = strtolower(trim((string) $product->base_unit));
                    $stockUnit = strtolower(trim((string) $product->stock_unit));
                    $legacyFactor = (float) $product->unit_conversion_factor;

                    if ($legacyFactor > 0 && $baseUnit === $stockUnit) {
                        $product->stock_quantity = round(
                            (float) $product->stock_base_quantity,
                            3
                        );
                    } elseif ($legacyFactor > 0) {
                        $product->stock_quantity = round(
                            (float) $product->stock_base_quantity / $legacyFactor,
                            3
                        );
                    }

                    $product->save();
                } else {
                    $product->decrement(
                        'stock_kg',
                        $item['quantity_kg']
                    );
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'brand' => $product->brand,
                    'unit_type' => $item['unit_type'],
                    'quantity_unit' => $item['unit_type'],
                    'quantity' => $item['quantity'],
                    'sack_weight_kg' => $item['sack_weight_kg'],
                    'quantity_kg' => $item['quantity_kg'] ?? 0,
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'unit_cost' => $item['unit_cost'],
                ]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'movement_type' => 'sale',
                    'quantity' => $item['is_generic']
                        ? -(float) $item['base_quantity']
                        : -$item['quantity'],
                    'unit' => $item['movement_unit'],
                    'quantity_kg' => $item['is_generic']
                        ? 0
                        : -($item['quantity_kg'] ?? 0),
                    'sacks' => !$item['is_generic']
                        && $item['unit_type'] === 'sack'
                            ? $item['quantity']
                            : 0,
                    'movement_date' => $now->toDateString(),
                    'notes' => 'Manual transaction ' . $orderNumber,
                    'created_by' => $request->user()->id,
                ]);
            }

            return $order;
        }, 3);

        $order->items()
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->unique()
            ->each(function ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    LowStockAlertNotifier::check($product);
                }
            });

        return redirect()
            ->route('admin.manual-transactions.receipt', $order)
            ->with('success', 'Transaction ' . $order->order_number . ' saved. Stock has been updated.');
    }

    public function receipt(Order $order)
    {
        if (
            $order->delivery_method !== 'pickup'
            || !str_contains((string) $order->admin_notes, 'Manual transaction')
        ) {
            abort(404);
        }

        $order->load(['items', 'payments.verifiedBy']);

        return view('admin.manual-transactions.receipt', compact('order'));
    }
}