<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\ProductVariant;

/**
 * Projects the Main Product variant into legacy product columns used by
 * existing catalog, storefront and order consumers.
 */
interface ProductVariantProjectionContract
{
    public function fromRows(array $variants): array;

    public function fromVariant(ProductVariant $variant): array;
}
