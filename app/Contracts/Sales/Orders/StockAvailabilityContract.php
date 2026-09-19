<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

interface StockAvailabilityContract
{
    public function ensureAvailable(
        Product $product,
        float $requestedQuantity,
        ?ProductVariant $variant = null
    ): void;
}
