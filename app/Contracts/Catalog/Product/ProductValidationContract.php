<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

interface ProductValidationContract
{
    public function extend(array $rules, ?Product $product = null): array;
}
