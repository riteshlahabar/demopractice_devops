<?php

namespace App\Contracts\Sales\Orders;

use App\Data\Sales\Orders\ResolvedOrderProduct;

interface OrderProductResolverContract
{
    public function resolve(
        string $orderType,
        int $productId,
        ?int $variantId
    ): ResolvedOrderProduct;
}
