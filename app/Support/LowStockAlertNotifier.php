<?php

namespace App\Support;

use App\Models\Product;

class LowStockAlertNotifier
{
    public static function check(Product $product): void
    {
        if (!$product->is_active) {
            return;
        }

        $generic = ($product->inventory_type ?? 'rice') === 'generic';
        $stock = $generic
            ? (float) $product->stock_base_quantity
            : (float) $product->stock_kg;
        $threshold = $generic
            ? (float) $product->low_stock_threshold_quantity
            : (float) $product->low_stock_threshold;
        $unit = $generic
            ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
            : 'kg';

        if ($stock <= 0) {
            if (!$product->out_of_stock_alerted_at) {
                UserAlertNotifier::admins(
                    'Product out of stock',
                    $product->name . ' is now out of stock.',
                    route('admin.products.edit', $product),
                );
                $product->out_of_stock_alerted_at = now();
            }
            $product->saveQuietly();
            return;
        }

        if ($product->out_of_stock_alerted_at) {
            $product->out_of_stock_alerted_at = null;
        }

        if ($stock <= $threshold && !$product->low_stock_alerted_at) {
            UserAlertNotifier::admins(
                'Low stock alert',
                sprintf(
                    '%s has %s %s remaining (threshold: %s %s).',
                    $product->name,
                    number_format($stock, 2),
                    $unit,
                    number_format($threshold, 2),
                    $unit
                ),
                route('admin.inventory.index'),
            );
            $product->low_stock_alerted_at = now();
        } elseif ($stock > $threshold && $product->low_stock_alerted_at) {
            $product->low_stock_alerted_at = null;
        }

        $product->saveQuietly();
    }
}