<?php

namespace App\Contracts\Sales\Orders;

interface OrderLineBuilderContract
{
    public function build(string $orderType, array $items): array;
}
