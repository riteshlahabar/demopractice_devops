<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Sales\Order;
use App\Models\User;

interface StockReservationContract
{
    public function reserve(
        Order $order,
        array $lineItems,
        ?User $actor
    ): void;
}
