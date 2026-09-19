<?php

namespace App\Contracts\Sales\Orders;

interface OrderCheckoutMapperContract
{
    public function map(array $checkoutData): array;
}
