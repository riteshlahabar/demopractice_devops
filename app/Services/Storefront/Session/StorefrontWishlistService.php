<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionProductRepositoryContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontSessionProductRulesContract;
use App\Contracts\Storefront\Session\StorefrontWishlistContract;
use App\Models\Catalog\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class StorefrontWishlistService implements StorefrontWishlistContract
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontSessionProductRepositoryContract $products,
        private readonly StorefrontSessionProductRulesContract $rules
    ) {}

    public function add(Request $request, Product $product): void
    {
        $this->rules->assertVisible($product, $this->identity->audience($request));

        $wishlist = $this->wishlist($request);
        if (! in_array($product->id, $wishlist, true)) {
            $wishlist[] = $product->id;
        }

        $this->store($request, $wishlist);
    }

    public function remove(Request $request, int $productId): void
    {
        $wishlist = collect($this->wishlist($request))
            ->reject(fn (int $storedProductId): bool => $storedProductId === $productId)
            ->values()
            ->all();

        $this->store($request, $wishlist);
    }

    public function toggle(Request $request, Product $product): bool
    {
        if ($this->has($request, $product->id)) {
            $this->remove($request, $product->id);

            return false;
        }

        $this->add($request, $product);

        return true;
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(StorefrontSessionKeys::WISHLIST);
    }

    public function wishlist(Request $request): array
    {
        $stored = $request->session()->get(StorefrontSessionKeys::WISHLIST, []);
        if (! is_array($stored)) {
            return [];
        }

        return collect($stored)
            ->map(fn ($productId): int => (int) $productId)
            ->filter(fn (int $productId): bool => $productId > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function has(Request $request, int $productId): bool
    {
        return in_array($productId, $this->wishlist($request), true);
    }

    public function summary(Request $request): array
    {
        $wishlist = collect($this->wishlist($request));
        $products = $this->productsForWishlist($request, $wishlist->all());
        $items = $wishlist
            ->map(fn (int $productId): ?Product => $products->get($productId))
            ->filter()
            ->values();

        return [
            'items' => $items,
            'count' => $items->count(),
            'ids' => $items->pluck('id')->all(),
        ];
    }

    private function store(Request $request, array $wishlist): void
    {
        $request->session()->put(StorefrontSessionKeys::WISHLIST, array_values($wishlist));
    }

    private function productsForWishlist(Request $request, array $wishlist): Collection
    {
        $productIds = collect($wishlist)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return $this->products->visibleByIds(
            $this->identity->audience($request),
            $productIds->all()
        );
    }
}
