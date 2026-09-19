<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\ApiController;
use App\Models\Engagement\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, read-only review feed for one product.
 *
 * Only approved rows are exposed, and the reviewer is reduced to a display
 * name so browsing a product page never leaks another customer's contact
 * details.
 */
class ProductReviewController extends ApiController
{
    public function index(Request $request, int $product): JsonResponse
    {
        $reviews = ProductReview::query()
            ->approved()
            ->where('product_id', $product)
            ->with('user:id,name')
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        $summary = ProductReview::query()
            ->approved()
            ->where('product_id', $product)
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')
            ->first();

        return $this->success([
            'reviews' => collect($reviews->items())->map(fn (ProductReview $review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'reviewer' => $review->user?->name,
                'verified_purchase' => $review->order_id !== null,
                'created_at' => $review->created_at?->toIso8601String(),
            ])->all(),
            'summary' => [
                'total' => (int) ($summary->total ?? 0),
                'average' => round((float) ($summary->average ?? 0), 2),
            ],
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
            ],
        ]);
    }
}
