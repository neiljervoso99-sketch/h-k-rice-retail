<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Order $order, Product $product): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);
        abort_unless($order->status === 'completed', 403);
        abort_unless($order->items()->where('product_id', $product->id)->exists(), 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $existingReview = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->where('order_id', $order->id)
            ->first();

        $photoPath = $existingReview?->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('product-reviews', 'public');
        }

        ProductReview::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'order_id' => $order->id,
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
                'photo_path' => $photoPath,
                'is_approved' => false,
            ]
        );

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Review submitted. It will appear after store approval.');
    }
}