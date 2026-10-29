<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSellingUnit;

class ProfitPricingController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'sellingUnits' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('conversion_factor')
                ->orderBy('unit_name'),
        ])
            ->orderBy('name')
            ->get();

        $pricingRows = $products->flatMap(function (Product $product) {
            $units = $product->inventory_type === 'generic'
                ? $product->sellingUnits
                : collect([null]);

            if ($units->isEmpty()) {
                $units = collect([null]);
            }

            return $units->map(function (?ProductSellingUnit $unit) use ($product) {
                $isGeneric = $product->inventory_type === 'generic';
                $cost = $isGeneric
                    ? (
                        $unit?->cost_price
                        ?? (
                            $product->cost_per_unit !== null
                                ? (float) $product->cost_per_unit
                                    * (float) ($unit?->conversion_factor ?? 1)
                                : null
                        )
                    )
                    : (
                        $product->cost_per_sack
                        ?? (
                            $product->cost_per_unit !== null
                                && (float) ($product->sack_weight_kg ?? 0) > 0
                                ? (float) $product->cost_per_unit
                                    * (float) $product->sack_weight_kg
                                : null
                        )
                    );
                $profitPercentage = $product->profit_percentage ?? 15;
                $suggestedPrice = $cost !== null && $profitPercentage !== null
                    ? (float) $cost * (1 + (float) $profitPercentage / 100)
                    : null;

                return [
                    'productName' => $product->name,
                    'unit' => $isGeneric
                        ? ($unit?->unit_name ?? 'Not set')
                        : ($product->sack_weight_kg
                            ? number_format($product->sack_weight_kg).' kg sack'
                            : 'Sack'),
                    'currentPrice' => $isGeneric
                        ? $unit?->selling_price
                        : $product->price_per_sack,
                    'cost' => $cost,
                    'profitPercentage' => $profitPercentage,
                    'suggestedPrice' => $suggestedPrice,
                    'estimatedProfit' => $suggestedPrice !== null
                        ? $suggestedPrice - (float) $cost
                        : null,
                ];
            });
        });

        return view('admin.profit-pricing.index', compact('pricingRows'));
    }
}