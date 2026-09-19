<?php

namespace App\Services\Sales\Access;

use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * The single place that decides which orders an account may see.
 *
 * Customers own orders through `customer_id`, dealers through `dealer_id`, and
 * a salesman sees the orders they raised or that belong to a dealer assigned
 * to them. Centralising it means a new endpoint cannot accidentally invent a
 * looser rule, which is the usual way an ownership check goes wrong.
 */
class OrderOwnershipScope
{
    public function forUser(User $user): Builder
    {
        $query = Order::query();

        return match ($user->role) {
            User::ROLE_CUSTOMER => $query->where('customer_id', $user->id),
            User::ROLE_DEALER => $query->where('dealer_id', $user->id),
            User::ROLE_SALESMAN => $query->where(function (Builder $inner) use ($user): void {
                $inner->where('salesman_id', $user->id)
                    ->orWhereIn('dealer_id', $user->assignedDealers()->select('user_id'));
            }),
            User::ROLE_ADMIN => $query,
            // An unknown role gets an intentionally empty result rather than
            // an unscoped one: fail closed, never open.
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * Resolves one order the user is allowed to see, or null.
     */
    public function find(User $user, int $orderId, array $with = []): ?Order
    {
        return $this->forUser($user)->with($with)->whereKey($orderId)->first();
    }
}
