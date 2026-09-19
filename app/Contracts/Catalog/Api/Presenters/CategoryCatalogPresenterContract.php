<?php

namespace App\Contracts\Catalog\Api\Presenters;

use App\Models\Catalog\Category;

/**
 * Converts Category domain model to API representation.
 */
interface CategoryCatalogPresenterContract
{
    public function present(
        Category $category
    ): array;
}
