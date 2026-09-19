<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

interface ProductStockContract
{
    public function createOpeningStock(Product $product, ?array $stock): void;

    public function syncVariantOpeningStock(Product $product, ProductVariant $variant, array $row): void;
}
