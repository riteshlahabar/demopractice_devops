<?php

namespace App\Contracts\Catalog\Product;

use App\Data\Catalog\ProductSaveData;
use App\Models\Catalog\Product;

interface ProductWorkflowContract
{
    public function save(ProductSaveData $data, ?Product $product = null): Product;
}
