<?php

namespace App\Contracts\Sales\Orders;

use App\Models\User;

interface DealerOrderContextContract
{
    public function assignedSalesman(User $dealer): User;

    public function assertAssignedToSalesman(
        User $dealer,
        User $salesman
    ): void;
}
