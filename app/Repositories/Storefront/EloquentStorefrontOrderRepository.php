<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontOrderRepositoryContract;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentStorefrontOrderRepository implements StorefrontOrderRepositoryContract
{
    public function recent(User $user): Collection
    {
        return $this->query($user)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function find(User $user, int $orderId): ?Order
    {
        return $this->query($user)->find($orderId);
    }

    public function tracked(User $user, string $requestedOrder): ?Order
    {
        return $this->query($user)
            ->where(function (Builder $builder) use ($requestedOrder): void {
                $builder->where('order_no', $requestedOrder);

                if (ctype_digit($requestedOrder)) {
                    $builder->orWhereKey((int) $requestedOrder);
                }
            })
            ->latest()
            ->first();
    }

    public function latest(User $user): ?Order
    {
        return $this->query($user)->latest()->first();
    }

    private function query(User $user): Builder
    {
        $query = Order::query()
            ->with([
                'items.product.images',
                'items.product.translations',
                'items.variant',
                'invoice',
                'dispatches',
                'salesman',
            ]);

        if ($user->role === User::ROLE_DEALER) {
            $query->where('dealer_id', $user->id);
        } else {
            $query->where('customer_id', $user->id);
        }

        return $query;
    }
}
