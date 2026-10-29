<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(): View
    {
        $reviews = ProductReview::query()
            ->with(['product:id,name', 'user:id,name', 'order:id,order_number'])
            ->latest()
            ->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(ProductReview $review): RedirectResponse
    {
        $review->is_approved = true;
        $review->save();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review approved and published.');
    }
}