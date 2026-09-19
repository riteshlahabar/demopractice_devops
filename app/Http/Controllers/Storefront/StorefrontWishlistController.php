<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontWishlistContract;
use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontWishlistController extends Controller
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontWishlistContract $wishlist
    ) {}

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = $this->wishlistProduct((int) $validated['product_id']);
        $this->wishlist->add($request, $product);

        return $this->response(
            $request,
            true,
            $product->translatedName().' added to wishlist.'
        );
    }

    public function remove(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        $this->wishlist->remove($request, $productId);

        return $this->response(
            $request,
            false,
            'Item removed from wishlist.'
        );
    }

    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $this->applyStoreLocale($request);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = $this->wishlistProduct((int) $validated['product_id']);
        $inWishlist = $this->wishlist->toggle($request, $product);

        return $this->response(
            $request,
            $inWishlist,
            $inWishlist
                ? $product->translatedName().' added to wishlist.'
                : $product->translatedName().' removed from wishlist.'
        );
    }

    private function applyStoreLocale(Request $request): void
    {
        $locale = (string) $request->session()->get('store_locale', 'en');

        if ($locale !== '') {
            app()->setLocale($locale);
        }
    }

    private function wishlistProduct(int $productId): Product
    {
        return Product::query()
            ->with(['category.translations', 'brand', 'unit', 'images', 'translations', 'inventoryBatches'])
            ->findOrFail($productId);
    }

    private function response(
        Request $request,
        bool $inWishlist,
        string $message
    ): JsonResponse|RedirectResponse {
        $summary = $this->wishlist->summary($request);
        $audience = $this->identity->audience($request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist,
                'count' => (int) ($summary['count'] ?? 0),
                'ids' => array_values(array_map('intval', $summary['ids'] ?? [])),
                'items' => collect($summary['items'] ?? collect())
                    ->take(3)
                    ->map(function (Product $product) use ($audience): array {
                        $price = (float) ($audience === 'dealer' ? $product->dealer_price : $product->customer_price);

                        return [
                            'id' => $product->id,
                            'name' => $product->translatedName(),
                            'product_url' => route('store.product', ['product' => $product->id]),
                            'image_url' => $product->storefront_image_url,
                            'price' => $price,
                        ];
                    })
                    ->values()
                    ->all(),
            ]);
        }

        return back()->with('success', $message);
    }
}
