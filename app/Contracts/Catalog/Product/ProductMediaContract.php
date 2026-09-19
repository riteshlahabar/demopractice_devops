<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

interface ProductMediaContract
{
    public function sync(Product $product, array $media): void;

    public function formData(Product $product): array;
}
