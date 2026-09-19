<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontOrderRepositoryContract;
use App\Contracts\Storefront\StorefrontOrderContextContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Models\User;
use Illuminate\Http\Request;

final class StorefrontOrderContextService implements StorefrontOrderContextContract
{
    public function __construct(
        private readonly StorefrontOrderRepositoryContract $orders,
        private readonly StorefrontSessionContextContract $session
    ) {}

    public function context(Request $request, ?User $user): array
    {
        if (! $user) {
            return [
                'orders' => collect(),
                'lastOrder' => null,
                'trackedOrder' => null,
            ];
        }

        $orders = $this->orders->recent($user);
        $orderId = $this->session->lastOrderId($request);
        $lastOrder = $orderId ? $this->orders->find($user, $orderId) : null;
        $requestedOrder = trim((string) $request->query('order'));
        $trackedOrder = $requestedOrder !== ''
            ? $this->orders->tracked($user, $requestedOrder)
            : null;

        return [
            'orders' => $orders,
            'lastOrder' => $lastOrder,
            'trackedOrder' => $trackedOrder ?: ($lastOrder ?: $this->orders->latest($user)),
        ];
    }
}
