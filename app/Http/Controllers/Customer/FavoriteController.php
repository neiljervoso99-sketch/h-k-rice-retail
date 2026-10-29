<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()
            ->favoriteProducts()
            ->where('is_active', true)
            ->with(['sellingUnits' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('products.name')
            ->get();

        return view('customer.favorites.index', compact('products'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $request->user()->favoriteProducts()->syncWithoutDetaching([$product->id]);

        return back()->with('success', $product->name . ' was saved to your favorites.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->favoriteProducts()->detach($product->id);

        return back()->with('success', $product->name . ' was removed from your favorites.');
    }
}