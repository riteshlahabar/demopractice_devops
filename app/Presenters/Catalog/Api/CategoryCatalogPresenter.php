<?php

namespace App\Presenters\Catalog\Api;

use App\Contracts\Catalog\Api\Presenters\CategoryCatalogPresenterContract;
use App\Models\Catalog\Category;

/**
 * SRP:
 * Category API response mapping only.
 */
final class CategoryCatalogPresenter implements CategoryCatalogPresenterContract
{
    public function present(
        Category $category
    ): array {
        return [
            'id' => $category->id,

            'name' => $category->storefront_name,

            'slug' => $category->slug,

            'image_url' => $category->storefront_image_url,

            'products_count' => (int) (
                $category->products_count
                ?? 0
            ),
        ];
    }
}
