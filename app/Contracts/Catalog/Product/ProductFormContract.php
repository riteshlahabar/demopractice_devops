<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

interface ProductFormContract
{
    public function augmentData(Product $product, array $data): array;

    public function augmentOptions(array $options): array;
}
