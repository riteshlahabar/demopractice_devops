<?php

namespace App\Contracts\Sales\Orders;

interface OrderNumberGeneratorContract
{
    public function next(string $orderType): string;
}
