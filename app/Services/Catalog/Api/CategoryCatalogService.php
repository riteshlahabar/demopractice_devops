<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\CategoryCatalogContract;
use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Contracts\Catalog\Api\Repositories\CategoryCatalogRepositoryContract;
use App\Models\Catalog\Category;

/**
 * SRP:
 * Category Catalog business workflow only.
 *
 * DIP:
 * Does not know Eloquent, Cache facade,
 * or concrete presenter implementation.
 */
final class CategoryCatalogService implements CategoryCatalogContract
{
    public function __construct(
        private readonly CategoryCatalogRepositoryContract $categories,
        private readonly CategoryCatalogPresenterContract $presenter,
        private readonly CatalogCacheContract $cache
    ) {}

    public function categories(
        string $locale,
        string $audience,
        bool $fresh
    ): array {
        $cacheKey =
            'catalog.categories.'
            .$this->cache->version()
            .'.'.$locale
            .'.'.$audience
            .'.array';

        return $this->cache->remember(
            $cacheKey,
            $fresh,
            fn (): array => $this->categories
                ->activeForCatalog(
                    $locale,
                    $audience
                )
                ->map(
                    fn (
                        Category $category
                    ): array => $this->presenter
                        ->present(
                            $category
                        )
                )
                ->values()
                ->all()
        );
    }
}
