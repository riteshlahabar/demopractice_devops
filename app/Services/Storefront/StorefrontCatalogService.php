<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontCatalogRepositoryContract;
use App\Contracts\Storefront\StorefrontCatalogContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\CompanySetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class StorefrontCatalogService implements StorefrontCatalogContract
{
    public function __construct(
        private readonly StorefrontCatalogRepositoryContract $catalog
    ) {}

    public function categories(string $audience): Collection
    {
        return $this->catalog->categories($audience, 18);
    }

    public function shopProducts(
        string $audience,
        ?string $productType,
        ?string $search
    ): LengthAwarePaginator {
        return $this->catalog->shopProducts($audience, $productType, $search, 24);
    }

    public function defaultProducts(string $audience): Collection
    {
        return $this->catalog->defaultProducts($audience, 24);
    }

    public function categoryProducts(Category $category, string $audience): LengthAwarePaginator
    {
        return $this->catalog->categoryProducts($category, $audience, 24);
    }

    public function productDetails(Product $product, string $audience): array
    {
        $this->catalog->loadProduct($product);
        $visibleColumn = $audience === 'dealer'
            ? 'is_visible_to_dealers'
            : 'is_visible_to_customers';

        $relatedProducts = $product->relatedProductLinks
            ->sortBy('sort_order')
            ->pluck('relatedProduct')
            ->filter(function (?Product $relatedProduct) use ($visibleColumn): bool {
                return $relatedProduct
                    && $relatedProduct->is_active
                    && (bool) $relatedProduct->{$visibleColumn};
            })
            ->values();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = $this->catalog->fallbackRelatedProducts($product, $audience, 8);
        }

        return [
            'storeProduct' => $product,
            'relatedProducts' => $relatedProducts,
            'trendingProducts' => $this->catalog->trendingProducts($product, $audience, 4),
            'companySetting' => $this->catalog->companySetting(),
        ];
    }

    public function companySetting(): ?CompanySetting
    {
        return $this->catalog->companySetting();
    }
}
