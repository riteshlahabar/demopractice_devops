<?php

namespace App\Contracts\Catalog\Api;

use App\Data\Catalog\Api\ProductCatalogFilters;

interface ProductCatalogContract
{
    public function products(ProductCatalogFilters $filters, bool $fresh): array;
}
