<?php

namespace App\Contracts\Catalog\Product;

/**
 * SRP: produces the unique product level SKU.
 *
 * SKU is no longer captured on the product form - it is entered per variant -
 * but `products.sku` is a NOT NULL UNIQUE column that orders, invoices and the
 * product search still rely on, so a stable code has to be generated on create.
 */
interface ProductSkuContract
{
    public function generate(?string $productName = null): string;
}
