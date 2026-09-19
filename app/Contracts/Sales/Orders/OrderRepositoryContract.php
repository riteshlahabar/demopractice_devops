<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Sales\Order;

interface OrderRepositoryContract
{
    public function create(array $attributes): Order;

    public function addItems(Order $order, array $lineItems): array;

    public function updateTotals(
        Order $order,
        float $subtotal,
        float $gstTotal
    ): void;

    public function loadForResult(Order $order): Order;
}
