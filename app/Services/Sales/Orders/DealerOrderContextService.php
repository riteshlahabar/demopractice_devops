<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\DealerOrderContextContract;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class DealerOrderContextService implements DealerOrderContextContract
{
    public function assignedSalesman(User $dealer): User
    {
        $salesman = $dealer->dealerProfile?->salesman;

        if (! $salesman) {
            throw ValidationException::withMessages([
                'dealer' => 'Dealer is not assigned to any salesman.',
            ]);
        }

        if (
            $salesman->role !== User::ROLE_SALESMAN
            || $salesman->status !== 'active'
        ) {
            throw ValidationException::withMessages([
                'dealer' => 'Assigned salesman is not active. Please contact admin.',
            ]);
        }

        return $salesman;
    }

    public function assertAssignedToSalesman(
        User $dealer,
        User $salesman
    ): void {
        if (
            (int) $dealer->dealerProfile?->salesman_id
            !== (int) $salesman->id
        ) {
            throw ValidationException::withMessages([
                'dealer' => 'Dealer is not assigned to this salesman.',
            ]);
        }
    }
}
