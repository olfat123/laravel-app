<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Enums\OrderStatusEnum;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'nullable|string|max:2000',
        ]);

        // Must have a delivered/completed order containing this product
        $hasReceivedOrder = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q
                ->where('user_id', auth()->id())
                ->whereIn('status', [
                    OrderStatusEnum::Completed->value,
                    OrderStatusEnum::Delivered->value,
                ])
            )
            ->exists();

        if (! $hasReceivedOrder) {
            return back()->withErrors(['review' => __('reviews.not_purchased')]);
        }

        // Prevent duplicate reviews
        $alreadyReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyReviewed) {
            return back()->withErrors(['review' => __('reviews.already_reviewed')]);
        }

        ProductReview::create([
            'product_id'  => $product->id,
            'user_id'     => auth()->id(),
            'rating'      => $request->rating,
            'body'        => $request->body,
            'is_approved' => false,
        ]);

        return back()->with('success', __('reviews.submitted'));
    }

    public function destroy(Product $product, ProductReview $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', __('reviews.deleted'));
    }
}
