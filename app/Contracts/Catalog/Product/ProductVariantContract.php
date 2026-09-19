<?php

namespace App\Contracts\Catalog\Product;

use App\Models\Catalog\Product;

/**
 * SRP: writing the posted variant rows back to the database.
 *
 * Reading them back for the form is a separate concern and lives in
 * ProductVariantFormDataContract, so a caller that only saves does not depend
 * on form shaping and vice versa.
 */
interface ProductVariantContract
{
    public function sync(Product $product, array $variants): void;
}
