<?php

namespace App\Contracts\Catalog\Product;

use App\Data\Catalog\ProductSaveData;

interface ProductInputContract
{
    public function make(array $prepared, array $input, array $files, array $module): ProductSaveData;
}
