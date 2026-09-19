<?php

namespace App\Contracts\Catalog\Api;

/**
 * High-level Category Catalog use case.
 */
interface CategoryCatalogContract
{
    public function categories(
        string $locale,
        string $audience,
        bool $fresh
    ): array;
}
