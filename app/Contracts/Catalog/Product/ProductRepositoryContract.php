<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

interface ProductRepositoryContract
{
    public function save(array $data, ?Product $product = null): Product;

    public function fresh(Product $product, array $relations = []): Product;
}
