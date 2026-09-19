<?php

namespace App\Contracts\Catalog\Api\Repositories;

use Illuminate\Support\Collection;

/**
 * Database operations required by Category Catalog only.
 */
interface CategoryCatalogRepositoryContract
{
    public function activeForCatalog(
        string $locale,
        string $audience
    ): Collection;
}
