<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

interface OrderPricingContract
{
    public function calculate(
        string $orderType,
        Product $product,
        ?ProductVariant $variant,
        float $quantity
    ): array;
}
