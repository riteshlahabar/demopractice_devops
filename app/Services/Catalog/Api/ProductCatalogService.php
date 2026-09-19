<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\Presenters\ProductCatalogPresenterContract;
use App\Contracts\Catalog\Api\ProductCatalogContract;
use App\Contracts\Catalog\Api\Repositories\ProductCatalogRepositoryContract;
use App\Data\Catalog\Api\ProductCatalogFilters;
use App\Models\Catalog\Product;

final class ProductCatalogService implements ProductCatalogContract
{
    public function __construct(
        private readonly ProductCatalogRepositoryContract $products,
        private readonly ProductCatalogPresenterContract $presenter,
        private readonly CatalogCacheContract $cache
    ) {}

    public function products(ProductCatalogFilters $filters, bool $fresh): array
    {
        $cacheKey = 'catalog.products.'
            .$this->cache->version().'.'
            // Names and units are translated, so each language has its own copy.
            .app()->getLocale().'.'
            .sha1(json_encode($filters->cachePayload()));

        return $this->cache->remember($cacheKey, $fresh, function () use ($filters): array {
            $paginator = $this->products->paginate($filters);
            $payload = $paginator->toArray();
            $payload['data'] = collect($paginator->items())
                ->map(fn (Product $product): array => $this->presenter->present($product))
                ->values()
                ->all();

            return $payload;
        });
    }
}
