<?php

namespace App\Contracts\Catalog\Api\Presenters;

use App\Models\Catalog\Product;

interface ProductCatalogPresenterContract
{
    public function present(Product $product): array;
}
