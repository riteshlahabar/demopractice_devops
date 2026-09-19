<?php

namespace App\Repositories\Catalog;

use App\Contracts\Catalog\Product\ProductRepositoryContract;
use App\Models\Catalog\Product;

final class EloquentProductRepository implements ProductRepositoryContract
{
    public function save(array $data, ?Product $product = null): Product
    {
        if ($product) {
            $product->fill($data)->save();

            return $product;
        }

        return Product::query()->create($data);
    }

    public function fresh(Product $product, array $relations = []): Product
    {
        return $product->fresh($relations);
    }
}
