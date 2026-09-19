<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

/**
 * SRP: shapes a product's variants into rows for the admin variant repeater.
 */
interface ProductVariantFormDataContract
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function rowsFor(Product $product): array;
}
