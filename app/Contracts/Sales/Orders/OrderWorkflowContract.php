<?php

namespace App\Contracts\Sales\Orders;

use App\Models\Sales\Order;
use App\Models\User;

interface OrderWorkflowContract
{
    public function createForCustomer(
        User $customer,
        array $items,
        ?string $notes = null,
        array $checkoutData = []
    ): Order;

    public function createForDealer(
        User $dealer,
        array $items,
        ?string $notes = null,
        array $checkoutData = []
    ): Order;

    public function createBySalesman(
        User $salesman,
        User $dealer,
        array $items,
        ?string $notes = null,
        array $checkoutData = []
    ): Order;
}
