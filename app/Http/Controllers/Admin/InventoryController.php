<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Support\LowStockAlertNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validateFilters($request);

        $products = Product::with('sellingUnits')
            ->orderBy('name')
            ->get();

        $lowStockProducts = $products->filter(function ($product) {
            if (!$product->is_active) {
                return false;
            }

            if (($product->inventory_type ?? 'rice') === 'generic') {
                return (float) $product->stock_base_quantity
                    <= (float) $product->low_stock_threshold_quantity;
            }

            return (float) $product->stock_kg
                <= (float) $product->low_stock_threshold;
        });

        $salesByProduct = InventoryMovement::query()
            ->where('movement_type', 'sale')
            ->whereNotNull('product_id')
            ->whereDate('movement_date', '>=', now()->subDays(29)->toDateString())
            ->whereDate('movement_date', '<=', now()->toDateString())
            ->get(['product_id', 'quantity', 'quantity_kg'])
            ->groupBy('product_id')
            ->map(fn ($movements) => $movements->sum(fn ($movement) =>
                abs((float) ($movement->quantity ?? $movement->quantity_kg ?? 0))
            ));

        $stockForecasts = $products
            ->filter(fn ($product) => $product->is_active)
            ->map(function ($product) use ($salesByProduct) {
                $generic = ($product->inventory_type ?? 'rice') === 'generic';
                $stock = $generic
                    ? (float) $product->stock_base_quantity
                    : (float) $product->stock_kg;
                $soldQuantity = (float) ($salesByProduct->get($product->id) ?? 0);

                if ($soldQuantity <= 0) {
                    return null;
                }

                $dailyAverage = $soldQuantity / 30;

                return [
                    'product' => $product,
                    'unit' => $generic
                        ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
                        : 'kg',
                    'stock' => $stock,
                    'sold_quantity' => $soldQuantity,
                    'daily_average' => $dailyAverage,
                    'days_remaining' => $dailyAverage > 0 ? $stock / $dailyAverage : null,
                    'next_week_demand' => $dailyAverage * 7,
                    'restock_gap' => max(0, ($dailyAverage * 7) - $stock),
                ];
            })
            ->filter()
            ->sortBy('days_remaining')
            ->values();

        $movements = $this->movementQuery($filters)
            ->with(['product', 'creator'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $stockInMovements = InventoryMovement::with(['product', 'creator'])
            ->where('movement_type', 'stock_in')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('admin.inventory.index', compact(
            'products',
            'lowStockProducts',
            'stockForecasts',
            'movements',
            'stockInMovements',
            'filters'
        ));
    }

    public function export(Request $request)
    {
        $filters = $this->validateFilters($request);

        $filename = 'inventory-movements-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Movement Date',
                'Product',
                'Movement Type',
                'Quantity',
                'Unit',
                'Legacy Quantity (kg)',
                'Sacks',
                'Notes',
                'Recorded By',
            ]);

            $this->movementQuery($filters)
                ->with(['product', 'creator'])
                ->orderByDesc('movement_date')
                ->orderByDesc('id')
                ->chunk(500, function ($movements) use ($output) {
                    foreach ($movements as $movement) {
                        fputcsv($output, [
                            $movement->movement_date?->format('Y-m-d') ?? '',
                            $movement->product_name
                                ?? $movement->product?->name
                                ?? '',
                            $movement->movement_type,
                            $movement->quantity,
                            $movement->unit,
                            $movement->quantity_kg,
                            $movement->sacks ?? 0,
                            $movement->notes ?? '',
                            $movement->creator?->name ?? '',
                        ]);
                    }
                });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'date_from' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'required_with:date_to',
            ],
            'date_to' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after_or_equal:date_from',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
            'movement_type' => [
                'nullable',
                'in:all,stock_in,sale,adjustment_in,adjustment_out',
            ],
        ]);
    }

    private function movementQuery(array $filters)
    {
        return InventoryMovement::query()
            ->when(
                !empty($filters['date_from']),
                fn ($query) => $query->whereDate(
                    'movement_date',
                    '>=',
                    $filters['date_from']
                )
            )
            ->when(
                !empty($filters['date_to']),
                fn ($query) => $query->whereDate(
                    'movement_date',
                    '<=',
                    $filters['date_to']
                )
            )
            ->when(
                !empty($filters['product_id']),
                fn ($query) => $query->where(
                    'product_id',
                    $filters['product_id']
                )
            )
            ->when(
                !empty($filters['movement_type'])
                    && $filters['movement_type'] !== 'all',
                fn ($query) => $query->where(
                    'movement_type',
                    $filters['movement_type']
                )
            );
    }

    /**
     * Record incoming inventory for rice or a generic product.
     */
    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            // Rice fields used by the existing stock-in form.
            'sacks' => [
                'required_if:inventory_type,rice',
                'nullable',
                'integer',
                'min:0',
                'max:100000',
            ],
            'extra_kg' => [
                'required_if:inventory_type,rice',
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            // Generic fields used by the generic stock-in form.
            'quantity' => [
                'required_if:inventory_type,generic',
                'nullable',
                'numeric',
                'gt:0',
                'max:1000000',
            ],
            'unit' => [
                'required_if:inventory_type,generic',
                'nullable',
                'string',
                'max:50',
            ],

            // The form may submit this hidden value, but the locked
            // product record remains the source of truth.
            'inventory_type' => [
                'required',
                Rule::in(['rice', 'generic']),
            ],

            'movement_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'supplier' => [
                'nullable',
                'string',
                'max:150',
            ],
            'batch_reference' => [
                'nullable',
                'string',
                'max:150',
            ],
            'delivery_reference' => [
                'nullable',
                'string',
                'max:150',
                'unique:inventory_movements,delivery_reference',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1500',
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::whereKey($validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => 'Cannot add stock to an inactive product.',
                ]);
            }

            $isRice = $product->inventory_type !== 'generic';

            if ($validated['inventory_type'] !== ($isRice ? 'rice' : 'generic')) {
                throw ValidationException::withMessages([
                    'product_id' => 'The selected product type does not match the submitted stock-in form.',
                ]);
            }

            if ($isRice) {
                if (blank($product->qr_code)) {
                    throw ValidationException::withMessages([
                        'product_id' => 'Register a QR code for this rice product before using rice stock-in.',
                    ]);
                }

                if (!$product->sack_weight_kg || $product->sack_weight_kg < 1) {
                    throw ValidationException::withMessages([
                        'product_id' => 'Set a valid sack weight for this rice product first.',
                    ]);
                }

                $sacks = (int) $validated['sacks'];
                $extraKg = (int) $validated['extra_kg'];

                $quantity = ($sacks * (int) $product->sack_weight_kg)
                    + $extraKg;

                if ($quantity < 1) {
                    throw ValidationException::withMessages([
                        'sacks' => 'Enter at least one sack or one kilogram.',
                    ]);
                }

                if ((float) $product->stock_kg + $quantity > 1000000) {
                    throw ValidationException::withMessages([
                        'sacks' => 'This quantity would exceed the maximum allowed rice stock.',
                    ]);
                }

                $product->increment('stock_kg', $quantity);

                $movementData = [
                    'quantity_kg' => $quantity,
                    'sacks' => $sacks,
                    'quantity' => $quantity,
                    'unit' => 'kg',
                ];
            } else {
                $unit = strtolower(trim($validated['unit']));

                $sellingUnit = ProductSellingUnit::query()
                    ->where('product_id', $product->id)
                    ->whereRaw('LOWER(TRIM(unit_name)) = ?', [$unit])
                    ->where('is_active', true)
                    ->first();

                if (!$sellingUnit) {
                    throw ValidationException::withMessages([
                        'unit' => 'Select an active selling unit configured for this product.',
                    ]);
                }

                $quantity = round((float) $validated['quantity'], 2);
                $factor = (float) $sellingUnit->conversion_factor;
                $currentStock = (float) $product->stock_base_quantity;

                if (!is_finite($factor) || $factor <= 0) {
                    throw ValidationException::withMessages([
                        'unit' => 'The selling-unit conversion factor is invalid.',
                    ]);
                }

                if (!is_finite($currentStock) || $currentStock < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The current base stock is invalid.',
                    ]);
                }

                $baseQuantity = round($quantity * $factor, 3);

                if (!is_finite($baseQuantity) || $baseQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The converted stock quantity is invalid.',
                    ]);
                }

                if ($currentStock + $baseQuantity > 1000000) {
                    throw ValidationException::withMessages([
                        'quantity' => 'This quantity would exceed the maximum allowed stock.',
                    ]);
                }

                $product->stock_base_quantity = round(
                    $currentStock + $baseQuantity,
                    3
                );

                $this->syncLegacyGenericStock($product);

                $movementData = [
                    'quantity_kg' => null,
                    'sacks' => 0,
                    'quantity' => $baseQuantity,
                    'unit' => strtolower(trim(
                        (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
                    )),
                ];
            }

            $details = [];

            if (!empty($validated['supplier'])) {
                $details[] = 'Supplier: ' . $validated['supplier'];
            }

            if (!empty($validated['batch_reference'])) {
                $details[] = 'Batch Reference: ' . $validated['batch_reference'];
            }

            if (!empty($validated['notes'])) {
                $details[] = 'Notes: ' . $validated['notes'];
            }

            $movementData += [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'movement_type' => 'stock_in',
                'movement_date' => $validated['movement_date'],
                'notes' => implode("\n", $details) ?: null,
                'created_by' => $request->user()->id,
                'delivery_reference' => !empty($validated['delivery_reference'])
                    ? trim($validated['delivery_reference'])
                    : null,
            ];

            InventoryMovement::create($movementData);
        });

        $updatedProduct = Product::find($validated['product_id']);
        if ($updatedProduct) {
            LowStockAlertNotifier::check($updatedProduct);
        }

        return redirect()
            ->route('admin.inventory.index')
            ->with('success', 'Stock received and inventory updated successfully.');
    }

    /**
     * Adjust rice inventory in kilograms and generic inventory in base units.
     */
    public function adjustment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'adjustment_type' => [
                'required',
                'in:adjustment_in,adjustment_out',
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:1000000',
            ],
            'extra_kg' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000000',
            ],
            'unit' => [
                'required',
                'string',
                'max:50',
            ],
            'movement_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'reason' => [
                'required',
                'in:physical_count,damage,wastage,other',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1500',
            ],
            'return_to' => [
                'nullable',
                Rule::in(['products']),
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::whereKey($validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $isRice = ($product->inventory_type ?? 'rice') !== 'generic';
            $unit = $isRice ? 'kg' : strtolower(trim($validated['unit']));

            $quantity = round((float) $validated['quantity'], 2);
            $extraKg = round((float) ($validated['extra_kg'] ?? 0), 2);

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Enter a quantity greater than zero.',
                ]);
            }

            $totalQuantity = $quantity + ($isRice ? $extraKg : 0);

            if ($totalQuantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Enter a quantity greater than zero.',
                ]);
            }

            if ($isRice) {
                if ($validated['unit'] !== 'kg') {
                    throw ValidationException::withMessages([
                        'unit' => 'Rice adjustments must use kilograms.',
                    ]);
                }

                $stockColumn = 'stock_kg';
                $currentStock = (float) $product->{$stockColumn};
                $movementQuantity = $totalQuantity;
                $movementUnit = 'kg';
                $quantityKgMovement = null;
            } else {
                if ($extraKg > 0) {
                    throw ValidationException::withMessages([
                        'extra_kg' => 'Additional kilograms can only be used for rice products.',
                    ]);
                }

                $sellingUnit = ProductSellingUnit::query()
                    ->where('product_id', $product->id)
                    ->whereRaw('LOWER(TRIM(unit_name)) = ?', [$unit])
                    ->where('is_active', true)
                    ->first();

                if (!$sellingUnit) {
                    throw ValidationException::withMessages([
                        'unit' => 'Select an active selling unit configured for this product.',
                    ]);
                }

                $factor = (float) $sellingUnit->conversion_factor;
                $currentStock = (float) $product->stock_base_quantity;

                if (!is_finite($factor) || $factor <= 0) {
                    throw ValidationException::withMessages([
                        'unit' => 'The selling-unit conversion factor is invalid.',
                    ]);
                }

                if (!is_finite($currentStock) || $currentStock < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The current base stock is invalid.',
                    ]);
                }

                $movementQuantity = round($quantity * $factor, 3);

                if (!is_finite($movementQuantity) || $movementQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The converted stock quantity is invalid.',
                    ]);
                }

                $movementUnit = strtolower(trim(
                    (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
                ));
                $quantityKgMovement = null;
            }

            if ($validated['adjustment_type'] === 'adjustment_out') {
                if ($movementQuantity > $currentStock) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The adjustment cannot remove more stock than is currently available.',
                    ]);
                }

                $signedQuantity = -$movementQuantity;
            } else {
                if ($currentStock + $movementQuantity > 1000000) {
                    throw ValidationException::withMessages([
                        'quantity' => 'This adjustment would exceed the maximum allowed stock.',
                    ]);
                }

                $signedQuantity = $movementQuantity;
            }

            if ($isRice) {
                $product->update([
                    'stock_kg' => round($currentStock + $signedQuantity, 2),
                ]);
            } else {
                $product->stock_base_quantity = round(
                    $currentStock + $signedQuantity,
                    3
                );
                $this->syncLegacyGenericStock($product);
            }

            $reasonLabels = [
                'physical_count' => 'Physical Count',
                'damage' => 'Damage',
                'wastage' => 'Wastage',
                'other' => 'Other',
            ];

            $notes = 'Reason: ' . $reasonLabels[$validated['reason']];

            if (!empty($validated['notes'])) {
                $notes .= "\nNotes: " . $validated['notes'];
            }

            if ($isRice && $extraKg > 0) {
                $notes .= "\nQuantity breakdown: "
                    . number_format($quantity, 2)
                    . ' kg + '
                    . number_format($extraKg, 2)
                    . ' additional kg';
            }

            $sacks = 0;

            if ($isRice && (float) $product->sack_weight_kg > 0) {
                $sacks = (int) floor(
                    $totalQuantity / (float) $product->sack_weight_kg
                );
            }

            InventoryMovement::create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'movement_type' => $validated['adjustment_type'],
                'quantity_kg' => $isRice ? $signedQuantity : $quantityKgMovement,
                'sacks' => $sacks,
                'quantity' => $signedQuantity,
                'unit' => $movementUnit,
                'movement_date' => $validated['movement_date'],
                'notes' => $notes,
                'created_by' => $request->user()->id,
            ]);
        });

        $updatedProduct = Product::find($validated['product_id']);
        if ($updatedProduct) {
            LowStockAlertNotifier::check($updatedProduct);
        }

        return redirect()
            ->route(
                ($validated['return_to'] ?? null) === 'products'
                    ? 'admin.products.index'
                    : 'admin.inventory.index'
            )
            ->with(
                'success',
                'Stock adjustment recorded and inventory updated successfully.'
            );
    }

    private function syncLegacyGenericStock(Product $product): void
    {
        $baseStock = (float) $product->stock_base_quantity;

        $baseUnit = strtolower(trim(
            (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
        ));

        $stockUnit = strtolower(trim((string) $product->stock_unit));
        $legacyFactor = (float) $product->unit_conversion_factor;

        if ($baseUnit === $stockUnit && abs($legacyFactor - 1.0) < 0.000001) {
            $product->stock_quantity = round($baseStock, 3);
        } elseif (is_finite($legacyFactor) && $legacyFactor > 0) {
            $product->stock_quantity = round($baseStock / $legacyFactor, 3);
        }

        $product->save();
    }

    /**
     * Look up a product by QR code.
     */
    public function qrLookup(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::with('sellingUnits')
            ->where('qr_code', trim($validated['qr_code']))
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'No active product matches this QR code.',
            ], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'inventory_type' => $product->inventory_type,
            'stock_unit' => $product->stock_unit,
            'stock_quantity' => $product->stock_quantity,
            'base_unit' => $product->base_unit,
            'stock_base_quantity' => $product->stock_base_quantity,
            'selling_units' => $product->sellingUnits()
                ->where('is_active', true)
                ->get([
                    'unit_name',
                    'conversion_factor',
                    'selling_price',
                    'cost_price',
                ]),
            'price_per_unit' => $product->price_per_unit,
            'sack_weight_kg' => $product->sack_weight_kg,
            'stock_kg' => $product->stock_kg,
            'qr_code' => $product->qr_code,
        ]);
    }
}