<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display orders belonging to the logged-in customer.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Display tracking details for a customer's own order.
     */
    public function show(Request $request, Order $order)
    {
        // Prevent customers from viewing another customer's order.
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            404
        );

        $order->load(['items', 'rider']);
        $reviewedProductIds = ProductReview::query()
            ->where('order_id', $order->id)
            ->where('user_id', $request->user()->id)
            ->pluck('product_id')
            ->all();

        return view('customer.orders.show', compact('order', 'reviewedProductIds'));
    }

    public function reorder(Request $request, Order $order)
    {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            404
        );

        $cart = $request->session()->get('cart', []);
        $added = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            $product = $item->product_id
                ? Product::query()->with('sellingUnits')->find($item->product_id)
                : null;

            if (!$product || !$product->is_active || !$product->price_confirmed) {
                $skipped++;
                continue;
            }

            $generic = ($product->inventory_type ?? 'rice') === 'generic';
            $unit = strtolower(trim((string) (
                $generic
                    ? ($item->quantity_unit ?: $item->unit_type ?: $product->stock_unit)
                    : ($item->unit_type ?: 'kg')
            )));
            $quantity = (float) $item->quantity;
            $key = (string) $product->id;

            if (!is_finite($quantity) || $quantity <= 0) {
                $skipped++;
                continue;
            }

            if (isset($cart[$key])) {
                if (($cart[$key]['unit'] ?? '') !== $unit) {
                    $skipped++;
                    continue;
                }

                $quantity += (float) ($cart[$key]['quantity'] ?? 0);
            }

            if ($generic) {
                $sellingUnit = $product->sellingUnits->first(fn (ProductSellingUnit $candidate) =>
                    $candidate->is_active
                    && strtolower($candidate->unit_name) === $unit
                );
                $factor = (float) ($sellingUnit?->conversion_factor ?? 0);

                if (
                    !$sellingUnit
                    || (float) $sellingUnit->selling_price <= 0
                    || $factor <= 0
                    || $quantity * $factor > (float) $product->stock_base_quantity
                ) {
                    $skipped++;
                    continue;
                }
            } else {
                $price = $unit === 'sack'
                    ? (float) $product->price_per_sack
                    : ($unit === 'kg' ? (float) $product->price_per_kg : 0);
                $quantityKg = $unit === 'sack'
                    ? $quantity * (float) $product->sack_weight_kg
                    : ($unit === 'kg' ? $quantity : 0);

                if (
                    $price <= 0
                    || $quantityKg <= 0
                    || $quantityKg > (float) $product->stock_kg
                ) {
                    $skipped++;
                    continue;
                }
            }

            $cart[$key] = [
                'unit' => $unit,
                'quantity' => $quantity,
            ];
            $added++;
        }

        $request->session()->put('cart', $cart);

        if ($added === 0) {
            return redirect()->route('cart.index')->with(
                'cart_error',
                'No items from that order could be added. Check current stock and prices.'
            );
        }

        $message = "{$added} item(s) from your order were added to your cart.";
        if ($skipped > 0) {
            $message .= " {$skipped} unavailable item(s) were skipped.";
        }

        return redirect()->route('cart.index')->with('cart_success', $message);
    }
}
