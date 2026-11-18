<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.products.index');
    }

    public function approve(ProductReview $review): RedirectResponse
    {
        $review->is_approved = true;
        $review->save();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Review approved and published.');
    }
}