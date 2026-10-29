<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Redirect the owner/admin to the admin dashboard.
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        // Validate storefront search and filter inputs.
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in([
                'name',
                'price_asc',
                'price_desc',
            ])],
        ]);

        // Retrieve active products for customers and guests.
        $query = Product::query()
            ->where('is_active', true);

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('rice_type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['type'])) {
            $type = $validated['type'];
            $query->where(function ($typeQuery) use ($type) {
                $typeQuery->where('rice_type', $type)
                    ->orWhere('category', $type);
            });
        }

        $sort = $validated['sort'] ?? 'name';

        $products = $query
            ->withCount(['reviews as approved_reviews_count' => fn ($reviews) => $reviews->where('is_approved', true)])
            ->withAvg(['reviews as approved_reviews_avg' => fn ($reviews) => $reviews->where('is_approved', true)], 'rating')
            ->with(['reviews' => fn ($reviews) => $reviews
                ->where('is_approved', true)
                ->with('user:id,name')
                ->latest()
                ->limit(2)])
            ->with(['sellingUnits' => fn ($units) => $units->where('is_active', true)])
            ->orderBy('name')
            ->get();

        if (in_array($sort, ['price_asc', 'price_desc'], true)) {
            $priceFor = function (Product $product): ?float {
                if (! $product->price_confirmed) {
                    return null;
                }

                if (($product->inventory_type ?? 'rice') === 'generic') {
                    $baseUnit = strtolower(trim(
                        (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
                    ));
                    $unit = $product->sellingUnits->first(
                        fn ($sellingUnit) => strtolower($sellingUnit->unit_name) === $baseUnit
                    );

                    return $unit && (float) $unit->selling_price > 0
                        ? (float) $unit->selling_price
                        : null;
                }

                return $product->price_per_kg !== null
                    && (float) $product->price_per_kg > 0
                    ? (float) $product->price_per_kg
                    : null;
            };

            $products = $products->sort(function (Product $a, Product $b) use ($priceFor, $sort) {
                $priceA = $priceFor($a);
                $priceB = $priceFor($b);

                if ($priceA === null || $priceB === null) {
                    if ($priceA !== $priceB) {
                        return $priceA === null ? 1 : -1;
                    }
                } elseif ($priceA !== $priceB) {
                    return $sort === 'price_asc'
                        ? $priceA <=> $priceB
                        : $priceB <=> $priceA;
                }

                return strcasecmp($a->name, $b->name);
            })->values();
        }

        $productTypes = Product::where('is_active', true)
            ->get(['rice_type', 'category'])
            ->map(fn ($product) => $product->rice_type ?: $product->category)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $favoriteProductIds = auth()->check() && auth()->user()->isCustomer()
            ? auth()->user()->favoriteProducts()->pluck('products.id')->all()
            : [];

        return view('home', compact(
            'products',
            'productTypes',
            'sort',
            'favoriteProductIds'
        ));
    }
}
