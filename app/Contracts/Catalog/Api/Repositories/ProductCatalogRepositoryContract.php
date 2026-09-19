<?php

namespace App\Contracts\Catalog\Api\Repositories;

use App\Data\Catalog\Api\ProductCatalogFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductCatalogRepositoryContract
{
    public function paginate(ProductCatalogFilters $filters): LengthAwarePaginator;
}
