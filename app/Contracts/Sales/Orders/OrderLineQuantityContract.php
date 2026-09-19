<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Catalog\ProductVariant;

interface OrderLineQuantityContract
{
    public function normalize(
        string $orderType,
        array $item,
        ?ProductVariant $variant
    ): array;
}
