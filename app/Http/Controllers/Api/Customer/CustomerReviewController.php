<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Engagement\ProductReview;
use App\Models\User;
use App\Services\Sales\Access\OrderOwnershipScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lets a customer write and read back their own product reviews.
 */
class CustomerReviewController extends ApiController
{
    public function __construct(private readonly OrderOwnershipScope $scope) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'reviews' => ProductReview::query()
                ->where('user_id', $user->id)
                ->with('product:id,name,sku')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'order_id' => ['nullable', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:4000'],
        ]);

        // A supplied order only counts as proof of purchase if it really is
        // this customer's order and really contains this product.
        $orderId = $this->verifiedOrderId($user, $validated);

        $review = ProductReview::query()->updateOrCreate(
            ['product_id' => $validated['product_id'], 'user_id' => $user->id],
            [
                'order_id' => $orderId,
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'] ?? null,
                // Reviews are held for moderation; nothing a customer types
                // reaches the storefront without an admin approving it.
                'status' => ProductReview::STATUS_PENDING,
            ]
        );

        return $this->success(
            ['review' => $review],
            'Thanks! Your review will appear once approved.',
            201
        );
    }

    private function verifiedOrderId(User $user, array $validated): ?int
    {
        if (empty($validated['order_id'])) {
            return null;
        }

        $order = $this->scope->find($user, (int) $validated['order_id'], ['items']);

        $hasProduct = $order?->items
            ->contains('product_id', (int) $validated['product_id']) ?? false;

        return $hasProduct ? $order->id : null;
    }
}
