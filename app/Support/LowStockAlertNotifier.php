<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class LowStockAlertNotifier
{
    public static function check(Product $product): void
    {
        $alerts = DB::transaction(function () use ($product) {
            $lockedProduct = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedProduct->is_active) {
                return [];
            }

            $generic = ($lockedProduct->inventory_type ?? 'rice') === 'generic';
            $stock = $generic
                ? (float) $lockedProduct->stock_base_quantity
                : (float) $lockedProduct->stock_kg;
            $threshold = $generic
                ? (float) $lockedProduct->low_stock_threshold_quantity
                : (float) $lockedProduct->low_stock_threshold;
            $unit = $generic
                ? ($lockedProduct->base_unit ?: $lockedProduct->stock_unit ?: 'unit')
                : 'kg';
            $alerts = [];

            if ($stock <= 0) {
                if (! $lockedProduct->out_of_stock_alerted_at) {
                    $alerts[] = [
                        'Product out of stock',
                        $lockedProduct->name.' is now out of stock.',
                        route('admin.products.edit', $lockedProduct),
                    ];
                    $lockedProduct->out_of_stock_alerted_at = now();
                }

                $lockedProduct->saveQuietly();

                return $alerts;
            }

            if ($lockedProduct->out_of_stock_alerted_at) {
                $lockedProduct->out_of_stock_alerted_at = null;
            }

            if ($stock <= $threshold && ! $lockedProduct->low_stock_alerted_at) {
                $alerts[] = [
                    'Low stock alert',
                    sprintf(
                        '%s has %s %s remaining (threshold: %s %s).',
                        $lockedProduct->name,
                        number_format($stock, 2),
                        $unit,
                        number_format($threshold, 2),
                        $unit
                    ),
                    route('admin.inventory.index'),
                ];
                $lockedProduct->low_stock_alerted_at = now();
            } elseif ($stock > $threshold && $lockedProduct->low_stock_alerted_at) {
                $lockedProduct->low_stock_alerted_at = null;
            }

            $lockedProduct->saveQuietly();

            return $alerts;
        }, 3);

        foreach ($alerts as [$title, $message, $url]) {
            UserAlertNotifier::admins($title, $message, $url);
        }
    }
}
