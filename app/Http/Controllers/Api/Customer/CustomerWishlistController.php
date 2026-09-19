<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Catalog\Product;
use App\Models\Engagement\Wishlist;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The saved-products list for a signed-in customer.
 */
class CustomerWishlistController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $items = Wishlist::query()
            ->where('user_id', $user->id)
            ->with('product:id,name,sku,mrp,customer_price,is_active')
            ->latest()
            ->get();

        return $this->success([
            'items' => $items,
            'count' => $items->count(),
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
        ]);

        $exists = Product::query()
            ->whereKey($validated['product_id'])
            ->where('is_active', true)
            ->where('is_visible_to_customers', true)
            ->exists();

        if (! $exists) {
            return $this->fail('Product not available.', 404);
        }

        // firstOrCreate keeps the endpoint idempotent, so a double tap on the
        // heart icon cannot violate the unique index or create a duplicate row.
        $item = Wishlist::query()->firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
        ]);

        return $this->success(['item' => $item], 'Added to wishlist.', 201);
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        Wishlist::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product)
            ->delete();

        return $this->success([], 'Removed from wishlist.');
    }
}
