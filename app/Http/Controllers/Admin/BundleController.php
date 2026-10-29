<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductSellingUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BundleController extends Controller
{
    public function index(): View
    {
        $bundles = ProductBundle::query()
            ->with('items.product')
            ->latest()
            ->paginate(20);
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.bundles.index', compact('bundles', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:2', 'max:10'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:1000'],
        ]);

        $products = Product::query()
            ->whereIn('id', collect($validated['items'])->pluck('product_id'))
            ->with('sellingUnits')
            ->get()
            ->keyBy('id');

        foreach ($validated['items'] as $item) {
            $this->validateBundleLine(
                $products->get($item['product_id']),
                $item['unit'],
                (float) $item['quantity']
            );
        }

        DB::transaction(function () use ($validated) {
            $bundle = ProductBundle::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            $bundle->items()->createMany($validated['items']);
        });

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle pack created.');
    }

    private function validateBundleLine(?Product $product, string $unit, float $quantity): void
    {
        if (!$product || !$product->is_active || !$product->price_confirmed) {
            throw ValidationException::withMessages(['items' => 'Every bundle item must be active with a confirmed price.']);
        }

        if (($product->inventory_type ?? 'rice') === 'generic') {
            $sellingUnit = $product->sellingUnits->first(fn (ProductSellingUnit $candidate) =>
                $candidate->is_active && strtolower($candidate->unit_name) === strtolower($unit)
            );
            $factor = (float) ($sellingUnit?->conversion_factor ?? 0);

            if (!$sellingUnit || (float) $sellingUnit->selling_price <= 0 || $factor <= 0 || $quantity * $factor > (float) $product->stock_base_quantity) {
                throw ValidationException::withMessages(['items' => "The selected unit or stock is unavailable for {$product->name}."]);
            }

            return;
        }

        $unit = strtolower($unit);
        $price = $unit === 'kg'
            ? (float) $product->price_per_kg
            : ($unit === 'sack' ? (float) $product->price_per_sack : 0);
        $quantityKg = $unit === 'kg'
            ? $quantity
            : ($unit === 'sack' ? $quantity * (float) $product->sack_weight_kg : 0);

        if (
            $price <= 0
            || ($unit === 'kg' && $quantity < 5)
            || ($unit === 'sack' && floor($quantity) !== $quantity)
            || $quantityKg <= 0
            || $quantityKg > (float) $product->stock_kg
        ) {
            throw ValidationException::withMessages(['items' => "The selected unit or stock is unavailable for {$product->name}."]);
        }
    }
}