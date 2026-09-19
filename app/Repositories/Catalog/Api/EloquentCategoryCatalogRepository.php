<?php

namespace App\Repositories\Catalog\Api;

use App\Contracts\Catalog\Api\Repositories\CategoryCatalogRepositoryContract;
use App\Models\Catalog\Category;
use Illuminate\Support\Collection;

/**
 * SRP:
 * Category Catalog database queries only.
 */
final class EloquentCategoryCatalogRepository implements CategoryCatalogRepositoryContract
{
    public function activeForCatalog(
        string $locale,
        string $audience
    ): Collection {
        return Category::query()
            ->with([
                'translations' => fn ($query) => $query->where(
                    'locale',
                    $locale
                ),
            ])
            ->withCount([
                'products' => fn ($query) => $query->visibleFor(
                    $audience
                ),
            ])
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'id'
            )
            ->get();
    }
}
