<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontCatalogRepositoryContract;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\CompanySetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentStorefrontCatalogRepository implements StorefrontCatalogRepositoryContract
{
    public function categories(string $audience, int $limit): Collection
    {
        return Category::query()
            ->with(['translations'])
            ->withCount(['products' => fn (Builder $query) => $query->visibleFor($audience)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function shopProducts(
        string $audience,
        ?string $productType,
        ?string $search,
        int $perPage
    ): LengthAwarePaginator {
        $query = $this->productQuery($audience);

        if ($productType !== null) {
            $query->where('product_type', $productType);
        }

        if ($search !== null) {
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query
            ->storefrontOrder()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function defaultProducts(string $audience, int $limit): Collection
    {
        return $this->productQuery($audience)
            ->storefrontOrder()
            ->limit($limit)
            ->get();
    }

    public function categoryProducts(Category $category, string $audience, int $perPage): LengthAwarePaginator
    {
        return $this->productQuery($audience)
            ->where('category_id', $category->getKey())
            ->storefrontOrder()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadProduct(Product $product): void
    {
        $product->load([
            'category',
            'brand',
            'unit',
            'images',
            'inventoryBatches',
            'media' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
            'variants' => fn ($query) => $query
                ->with('inventoryBatches')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
            'relatedProductLinks.relatedProduct.category.translations',
            'relatedProductLinks.relatedProduct.brand',
            'relatedProductLinks.relatedProduct.unit',
            'relatedProductLinks.relatedProduct.images',
            'relatedProductLinks.relatedProduct.translations',
            'relatedProductLinks.relatedProduct.inventoryBatches',
            'relatedProductLinks.relatedProduct.variants.inventoryBatches',
        ]);
    }

    public function fallbackRelatedProducts(Product $product, string $audience, int $limit): Collection
    {
        return $this->productQuery($audience)
            ->when(
                $product->category_id,
                fn (Builder $query) => $query->where('category_id', $product->category_id)
            )
            ->whereKeyNot($product->getKey())
            ->storefrontOrder()
            ->limit($limit)
            ->get();
    }

    public function trendingProducts(Product $product, string $audience, int $limit): Collection
    {
        return $this->productQuery($audience)
            ->where('is_trending', true)
            ->whereKeyNot($product->getKey())
            ->storefrontOrder()
            ->limit($limit)
            ->get();
    }

    public function companySetting(): ?CompanySetting
    {
        return CompanySetting::query()->first();
    }

    private function productQuery(string $audience): Builder
    {
        return Product::query()
            ->with([
                'category.translations',
                'brand',
                'unit',
                'images',
                'translations',
                'inventoryBatches',
                'variants.inventoryBatches',
            ])
            ->visibleFor($audience);
    }
}
