<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('sellingUnits')
            ->orderBy('name')
            ->get();

        $activeProducts = Product::where('is_active', true);

        $totalRiceStockKg = (clone $activeProducts)
            ->where(function ($query) {
                $query->whereNull('inventory_type')
                    ->orWhere('inventory_type', '!=', 'generic');
            })
            ->sum('stock_kg');

        $lowStockCount = (clone $activeProducts)
            ->where(function ($query) {
                $query->where(function ($rice) {
                    $rice->whereNull('inventory_type')
                        ->orWhere('inventory_type', '!=', 'generic');
                })
                    ->whereColumn('stock_kg', '<=', 'low_stock_threshold');
            })
            ->orWhere(function ($generic) {
                $generic->where('is_active', true)
                    ->where('inventory_type', 'generic')
                    ->whereColumn(
                        'stock_base_quantity',
                        '<=',
                        'low_stock_threshold_quantity'
                    );
            })
            ->count();

        $outOfStockCount = (clone $activeProducts)
            ->where(function ($query) {
                $query->where(function ($rice) {
                    $rice->whereNull('inventory_type')
                        ->orWhere('inventory_type', '!=', 'generic');
                })
                    ->where('stock_kg', '<=', 0);
            })
            ->orWhere(function ($generic) {
                $generic->where('is_active', true)
                    ->where('inventory_type', 'generic')
                    ->where('stock_base_quantity', '<=', 0);
            })
            ->count();

        return view('admin.products.index', compact(
            'products',
            'totalRiceStockKg',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function edit(Product $product)
    {
        $product->load('sellingUnits');

        return view('admin.products.edit', compact('product'));
    }

    public function store(Request $request)
    {
        $category = is_string($request->input('category'))
            ? trim($request->input('category'))
            : '';
        $riceType = is_string($request->input('rice_type'))
            ? trim($request->input('rice_type'))
            : '';
        $isRiceProduct = preg_match('/\brice\b/i', $category) === 1
            || $riceType !== '';

        $request->merge([
            'inventory_type' => $isRiceProduct ? 'rice' : 'generic',
        ]);

        $validated = $request->validate([
            'inventory_type' => [
                'required',
                Rule::in(['rice', 'generic']),
            ],

            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'variant_size' => ['nullable', 'string', 'max:80'],
            'variant_color' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'rice_type' => ['nullable', 'string', 'max:255'],

            'base_unit' => [
                'required_if:inventory_type,generic',
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9 _-]+$/',
            ],

            'initial_stock_quantity' => [
                'required_if:inventory_type,generic',
                'nullable',
                'numeric',
                'min:0',
                'max:1000000',
            ],

            'selling_units' => [
                'required_if:inventory_type,generic',
                'array',
                'min:1',
                'max:20',
            ],

            'selling_units.*.unit_name' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-zA-Z0-9 _-]+$/',
                'distinct:ignore_case',
            ],

            'selling_units.*.conversion_factor' => [
                'required',
                'numeric',
                'gt:0',
                'max:100000000',
            ],

            'selling_units.*.selling_price' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999.99',
            ],

            'selling_units.*.cost_price' => [
                'required_if:inventory_type,generic',
                'numeric',
                'gt:0',
                'max:9999999.99',
            ],

            'sack_weight_kg' => [
                'required_if:inventory_type,rice',
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],

            'price_per_kg' => [
                'required_if:inventory_type,rice',
                'nullable',
                'numeric',
                'gt:0',
                'max:9999999.99',
            ],

            'price_per_sack' => [
                'required_if:inventory_type,rice',
                'nullable',
                'numeric',
                'gt:0',
                'max:9999999.99',
            ],

            'cost_per_sack' => [
                'required_if:inventory_type,rice',
                'numeric',
                'gt:0',
                'max:9999999.99',
            ],

            'profit_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000',
            ],

            'low_stock_threshold' => [
                'required',
                'numeric',
                'min:0',
                'max:1000000',
            ],

            'qr_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'qr_code'),
            ],

            'image' => [
                'nullable',
                'image',
                'max:10240',
            ],

            'is_active' => ['required', 'boolean'],
        ]);

        $validated['profit_percentage'] = $validated['profit_percentage'] ?? 15;

        $isRice = $validated['inventory_type'] === 'rice';

        $threshold = (float) $validated['low_stock_threshold'];

        $validated['stock_kg'] = 0;
        $validated['low_stock_threshold'] = (int) round($threshold);
        $validated['low_stock_threshold_quantity'] = round($threshold, 2);
        $validated['price_confirmed'] = false;
        $validated['qr_code'] = isset($validated['qr_code'])
            ? trim($validated['qr_code'])
            : null;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        if ($isRice) {
            $validated['stock_unit'] = 'kg';
            $validated['price_per_unit'] = null;
            $validated['cost_per_unit'] = null;
            $validated['stock_quantity'] = 0;
            $validated['stock_base_quantity'] = 0;
            $validated['unit_conversion_factor'] = 1;
            $validated['base_unit'] = 'kg';

            unset(
                $validated['base_unit'],
                $validated['initial_stock_quantity'],
                $validated['selling_units']
            );

            Product::create($validated);
        } else {
            $baseUnit = strtolower(trim($validated['base_unit']));
            $initialStock = (float) $validated['initial_stock_quantity'];
            $sellingUnits = $validated['selling_units'];

            $hasBaseUnit = false;

            foreach ($sellingUnits as $unit) {
                $unitName = strtolower(trim($unit['unit_name']));
                $factor = (float) $unit['conversion_factor'];

                if ($unitName === $baseUnit && abs($factor - 1.0) < 0.000001) {
                    $hasBaseUnit = true;
                    break;
                }
            }

            if (! $hasBaseUnit) {
                throw ValidationException::withMessages([
                    'selling_units' => "Add '{$baseUnit}' as a selling unit with conversion factor 1. "
                        .'This is required so customers can buy using the base unit.',
                ]);
            }

            $validated['rice_type'] = null;
            $validated['sack_weight_kg'] = null;
            $validated['price_per_kg'] = null;
            $validated['price_per_sack'] = null;
            $validated['cost_per_sack'] = null;

            $validated['stock_unit'] = $baseUnit;
            $validated['stock_quantity'] = $initialStock;
            $validated['price_per_unit'] = null;
            $validated['cost_per_unit'] = null;
            $validated['base_unit'] = $baseUnit;
            $validated['unit_conversion_factor'] = 1;
            $validated['stock_base_quantity'] = round($initialStock, 3);
            $validated['low_stock_threshold_quantity'] = round(
                $threshold,
                3
            );

            unset(
                $validated['initial_stock_quantity'],
                $validated['selling_units']
            );

            DB::transaction(function () use ($validated, $sellingUnits) {
                $product = Product::create($validated);

                foreach ($sellingUnits as $unit) {
                    ProductSellingUnit::create([
                        'product_id' => $product->id,
                        'unit_name' => strtolower(trim($unit['unit_name'])),
                        'conversion_factor' => $unit['conversion_factor'],
                        'selling_price' => $unit['selling_price'],
                        'cost_price' => ($unit['cost_price'] ?? '') !== ''
                            ? $unit['cost_price']
                            : null,
                        'is_active' => true,
                    ]);
                }
            });
        }

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                $validated['name'].' created successfully.'
            );
    }

    public function update(Request $request, Product $product)
    {
        $isGeneric = $product->inventory_type === 'generic';

        if ($isGeneric) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'variant_size' => ['nullable', 'string', 'max:80'],
                'variant_color' => ['nullable', 'string', 'max:80'],
                'description' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
                'price_per_unit' => [
                    'sometimes', 'nullable', 'numeric', 'gt:0',
                    'max:9999999.99',
                ],
                'cost_per_unit' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999.99',
                ],
                'piece_price' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'gt:0',
                    'max:9999999.99',
                ],
                'piece_cost' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999.99',
                ],
                'selling_units' => ['sometimes', 'array', 'max:30'],
                'selling_units.*.id' => [
                    'nullable',
                    'integer',
                    'exists:product_selling_units,id',
                ],
                'selling_units.*.unit_name' => [
                    'required',
                    'string',
                    'max:30',
                ],
                'selling_units.*.conversion_factor' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'max:1000000',
                ],
                'selling_units.*.selling_price' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'max:9999999.99',
                ],
                'selling_units.*.cost_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999.99',
                ],
                'selling_units.*.is_active' => [
                    'sometimes',
                    'boolean',
                ],
                'stock_unit' => [
                    'sometimes', 'required', 'string', 'max:30',
                    Rule::in([
                        'piece', 'pack', 'bottle', 'box', 'can',
                        'bag', 'kg', 'gram', 'liter', 'ml',
                        'roll', 'pair', 'dozen',
                    ]),
                ],
                'low_stock_threshold_quantity' => [
                    'sometimes', 'required', 'numeric',
                    'min:0', 'max:1000000',
                ],
                'image' => [
                    'nullable',
                    'image',
                    'max:10240',
                ],
                'price_confirmed' => ['sometimes', 'boolean'],
                '_product_id' => [
                    'required', 'integer', 'in:'.$product->id,
                ],
            ]);

            $updates = [];
            foreach (['name', 'variant_size', 'variant_color', 'description', 'is_active'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updates[$field] = $validated[$field];
                }
            }

            foreach ([
                'price_per_unit',
                'cost_per_unit',
                'stock_unit',
            ] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updates[$field] = $validated[$field];
                }
            }

            if (array_key_exists('low_stock_threshold_quantity', $validated)) {
                $updates['low_stock_threshold_quantity'] =
                    round((float) $validated['low_stock_threshold_quantity'], 2);
            }

            if ($request->has('price_confirmed')) {
                $confirm = $request->boolean('price_confirmed');
                $baseUnitName = strtolower(trim(
                    (string) ($product->base_unit ?: $product->stock_unit)
                ));
                $submittedBaseUnit = collect(
                    $validated['selling_units'] ?? []
                )->first(function ($unit) use ($baseUnitName) {
                    return strtolower(trim($unit['unit_name']))
                        === $baseUnitName
                        && ! empty($unit['is_active']);
                });
                $unitPrice = $submittedBaseUnit['selling_price']
                    ?? $updates['price_per_unit']
                    ?? $product->price_per_unit;

                if (
                    $confirm
                    && (
                        $unitPrice === null
                        || (float) $unitPrice <= 0
                    )
                ) {
                    throw ValidationException::withMessages([
                        'price_confirmed' => 'Enter a valid unit price before confirming.',
                    ]);
                }

                $updates['price_confirmed'] = $confirm;
            }

            if ($request->hasFile('image')) {
                $updates['image'] = $request->file('image')
                    ->store('products', 'public');
            }

            if (array_key_exists('selling_units', $validated)) {
                $baseUnit = strtolower(trim(
                    (string) ($product->base_unit ?: $product->stock_unit)
                ));
                $unitNames = [];
                $hasActiveBaseUnit = false;

                foreach ($validated['selling_units'] as $index => $unit) {
                    $unitName = strtolower(trim($unit['unit_name']));

                    if (in_array($unitName, $unitNames, true)) {
                        throw ValidationException::withMessages([
                            "selling_units.$index.unit_name" => 'Selling unit names must be unique.',
                        ]);
                    }

                    $unitNames[] = $unitName;

                    if (
                        $unitName === $baseUnit
                        && ! empty($unit['is_active'])
                        && abs((float) $unit['conversion_factor'] - 1.0) < 0.000001
                    ) {
                        $hasActiveBaseUnit = true;
                    }
                }

                if (! $hasActiveBaseUnit) {
                    throw ValidationException::withMessages([
                        'selling_units' => "Keep '{$baseUnit}' active with conversion factor 1.",
                    ]);
                }
            }

            DB::transaction(function () use (
                $product,
                $updates,
                $validated
            ) {
                $product->update($updates);

                if (! array_key_exists('selling_units', $validated)) {
                    return;
                }

                $existingUnits = ProductSellingUnit::query()
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $submittedIds = [];
                $baseUnit = strtolower(trim(
                    (string) ($product->base_unit ?: $product->stock_unit)
                ));

                foreach ($validated['selling_units'] as $unitData) {
                    $unitName = strtolower(trim($unitData['unit_name']));
                    $unit = null;

                    if (! empty($unitData['id'])) {
                        $unit = $existingUnits->get((int) $unitData['id']);

                        if (! $unit) {
                            throw ValidationException::withMessages([
                                'selling_units' => 'A selling unit does not belong to this product.',
                            ]);
                        }

                        $submittedIds[] = $unit->id;
                    } else {
                        $unit = $existingUnits->first(function ($existing) use (
                            $unitName,
                            $submittedIds
                        ) {
                            return ! in_array($existing->id, $submittedIds, true)
                                && strtolower($existing->unit_name) === $unitName;
                        });

                        if (! $unit) {
                            $unit = new ProductSellingUnit([
                                'product_id' => $product->id,
                            ]);
                        } else {
                            $submittedIds[] = $unit->id;
                        }
                    }

                    $unit->unit_name = $unitName;
                    $unit->conversion_factor = $unitData['conversion_factor'];
                    $unit->selling_price = $unitData['selling_price'];
                    $unit->cost_price = $unitData['cost_price'] ?? null;
                    $unit->is_active = (bool) ($unitData['is_active'] ?? false);
                    $unit->save();

                    if ($unitName === $baseUnit && $unit->is_active) {
                        $product->price_per_unit = $unit->selling_price;
                        $product->cost_per_unit = $unit->cost_price;
                    }
                }

                $removedIds = $existingUnits->keys()
                    ->diff($submittedIds)
                    ->values();

                if ($removedIds->isNotEmpty()) {
                    ProductSellingUnit::query()
                        ->where('product_id', $product->id)
                        ->whereIn('id', $removedIds)
                        ->update(['is_active' => false]);
                }

                $product->save();
            });
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'variant_size' => ['nullable', 'string', 'max:80'],
                'variant_color' => ['nullable', 'string', 'max:80'],
                'description' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
                'price_per_kg' => [
                    'sometimes', 'nullable', 'numeric', 'gt:0',
                    'max:9999999.99',
                ],
                'price_per_sack' => [
                    'sometimes', 'nullable', 'numeric', 'gt:0',
                    'max:9999999.99',
                ],
                'sack_weight_kg' => [
                    'sometimes', 'nullable', 'integer', 'min:1', 'max:1000',
                ],
                'cost_per_sack' => [
                    'sometimes', 'nullable', 'numeric', 'min:0',
                    'max:9999999.99',
                ],
                'profit_percentage' => [
                    'sometimes', 'nullable', 'numeric', 'min:0', 'max:1000',
                ],
                'low_stock_threshold_sacks' => [
                    'sometimes', 'required', 'numeric', 'min:0', 'max:1000000',
                ],
                'image' => [
                    'nullable',
                    'image',
                    'max:10240',
                ],
                'price_confirmed' => ['sometimes', 'boolean'],
                '_product_id' => [
                    'required', 'integer', 'in:'.$product->id,
                ],
            ]);

            $sackWeight = array_key_exists('sack_weight_kg', $validated)
                ? $validated['sack_weight_kg']
                : $product->sack_weight_kg;

            $pricePerKg = $validated['price_per_kg']
                ?? $product->price_per_kg;
            $pricePerSack = $validated['price_per_sack']
                ?? $product->price_per_sack;

            if ($sackWeight === null || (float) $sackWeight <= 0) {
                throw ValidationException::withMessages([
                    'sack_weight_kg' => 'Set a valid sack weight.',
                ]);
            }

            $updates = [];
            foreach (['name', 'variant_size', 'variant_color', 'description', 'is_active'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updates[$field] = $validated[$field];
                }
            }

            foreach ([
                'price_per_kg',
                'price_per_sack',
                'sack_weight_kg',
                'cost_per_sack',
                'profit_percentage',
            ] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updates[$field] = $validated[$field];
                }
            }

            if (array_key_exists('low_stock_threshold_sacks', $validated)) {
                $thresholdKg = (float) $validated['low_stock_threshold_sacks']
                    * (float) $sackWeight;

                if ($thresholdKg > 1000000) {
                    throw ValidationException::withMessages([
                        'low_stock_threshold_sacks' => 'The converted threshold cannot exceed 1,000,000 kg.',
                    ]);
                }

                $updates['low_stock_threshold'] = (int) round($thresholdKg);
                $updates['low_stock_threshold_quantity'] = round($thresholdKg, 2);
            }

            if ($request->has('price_confirmed')) {
                $confirm = $request->boolean('price_confirmed');

                if (
                    $confirm
                    && ($pricePerKg === null || (float) $pricePerKg <= 0)
                    && ($pricePerSack === null || (float) $pricePerSack <= 0)
                ) {
                    throw ValidationException::withMessages([
                        'price_confirmed' => 'Enter at least one valid price before confirming.',
                    ]);
                }

                $updates['price_confirmed'] = $confirm;
            }

            if ($request->hasFile('image')) {
                $updates['image'] = $request->file('image')
                    ->store('products', 'public');
            }

            $product->update($updates);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $product->name.' updated successfully.');
    }

    public function destroy(Product $product)
    {
        $hasOrderHistory = OrderItem::where(
            'product_id',
            $product->id
        )->exists();

        if ($hasOrderHistory) {
            $product->update(['is_active' => false]);

            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    $product->name.
                    ' was hidden from the store because it has order history. Past orders were preserved.'
                );
        }

        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $name.' was permanently deleted.');
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'remove'])],
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
        ]);

        $removed = 0;
        $hidden = 0;
        $updated = 0;

        DB::transaction(function () use (
            $validated,
            &$removed,
            &$hidden,
            &$updated
        ) {
            $products = Product::query()
                ->whereIn('id', $validated['product_ids'])
                ->lockForUpdate()
                ->get();

            foreach ($products as $product) {
                if ($validated['action'] === 'activate') {
                    $product->update(['is_active' => true]);
                    $updated++;

                    continue;
                }

                if ($validated['action'] === 'deactivate') {
                    $product->update(['is_active' => false]);
                    $updated++;

                    continue;
                }

                if (OrderItem::where('product_id', $product->id)->exists()) {
                    $product->update(['is_active' => false]);
                    $hidden++;

                    continue;
                }

                $product->delete();
                $removed++;
            }
        });

        $message = match ($validated['action']) {
            'activate' => "$updated product(s) activated.",
            'deactivate' => "$updated product(s) deactivated.",
            default => "$removed product(s) permanently removed; "
            ."$hidden product(s) with order history were deactivated.",
        };

        return redirect()
            ->route('admin.products.index')
            ->with('success', $message);
    }

    public function updateQrCode(Request $request, Product $product)
    {
        $validated = $request->validate([
            'qr_code' => [
                'required',
                'string',
                'max:255',
                'unique:products,qr_code,'.$product->id,
            ],
        ]);

        $product->update([
            'qr_code' => trim($validated['qr_code']),
        ]);

        return back()->with(
            'success',
            'QR code updated for '.$product->name.'.'
        );
    }
}
