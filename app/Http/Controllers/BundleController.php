<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use App\Models\ProductSellingUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BundleController extends Controller
{
    public function index(): View
    {
        $bundles = ProductBundle::query()
            ->where('is_active', true)
            ->with(['items.product.sellingUnits'])
            ->latest()
            ->get()
            ->filter(fn (ProductBundle $bundle) => $bundle->items->count() >= 2);

        return view('bundles.index', compact('bundles'));
    }

    public function addToCart(Request $request, ProductBundle $bundle): RedirectResponse
    {
        abort_unless($bundle->is_active, 404);

        $bundle->load('items.product.sellingUnits');
        $cart = $request->session()->get('cart', []);
        $updatedCart = $cart;

        foreach ($bundle->items as $item) {
            $product = $item->product;
            $this->validateBundleLine($item, $product, $updatedCart);
        }

        foreach ($bundle->items as $item) {
            $id = (string) $item->product_id;
            $quantity = (float) $item->quantity;
            if (isset($updatedCart[$id])) {
                $quantity += (float) $updatedCart[$id]['quantity'];
            }
            $updatedCart[$id] = ['unit' => $item->unit, 'quantity' => $quantity];
        }

        $request->session()->put('cart', $updatedCart);

        return redirect()->route('cart.index')->with('cart_success', $bundle->name . ' was added to your cart.');
    }

    private function validateBundleLine(ProductBundleItem $item, ?Product $product, array $cart): void
    {
        if (!$product || !$product->is_active || !$product->price_confirmed) {
            throw ValidationException::withMessages(['cart' => 'A bundle item is unavailable or its price is not confirmed. No items were added.']);
        }

        $id = (string) $product->id;
        $quantity = (float) $item->quantity;
        if (isset($cart[$id])) {
            if (($cart[$id]['unit'] ?? '') !== $item->unit) {
                throw ValidationException::withMessages(['cart' => "{$product->name} is already in your cart with a different unit. No bundle items were added."]);
            }
            $quantity += (float) ($cart[$id]['quantity'] ?? 0);
        }

        if (($product->inventory_type ?? 'rice') === 'generic') {
            $sellingUnit = $product->sellingUnits->first(fn (ProductSellingUnit $candidate) =>
                $candidate->is_active && strtolower($candidate->unit_name) === strtolower($item->unit)
            );
            $factor = (float) ($sellingUnit?->conversion_factor ?? 0);
            if (!$sellingUnit || (float) $sellingUnit->selling_price <= 0 || $factor <= 0 || $quantity * $factor > (float) $product->stock_base_quantity) {
                throw ValidationException::withMessages(['cart' => "Insufficient stock or invalid unit for {$product->name}. No bundle items were added."]);
            }
            return;
        }

        $unit = strtolower($item->unit);
        $price = $unit === 'kg' ? (float) $product->price_per_kg : ($unit === 'sack' ? (float) $product->price_per_sack : 0);
        $quantityKg = $unit === 'kg' ? $quantity : ($unit === 'sack' ? $quantity * (float) $product->sack_weight_kg : 0);
        if ($price <= 0 || ($unit === 'kg' && $quantity < 5) || ($unit === 'sack' && floor($quantity) !== $quantity) || $quantityKg <= 0 || $quantityKg > (float) $product->stock_kg) {
            throw ValidationException::withMessages(['cart' => "Insufficient stock or invalid unit for {$product->name}. No bundle items were added."]);
        }
    }
}