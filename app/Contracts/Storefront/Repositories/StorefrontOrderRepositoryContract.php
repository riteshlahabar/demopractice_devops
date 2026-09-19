<?php

namespace App\Contracts\Storefront\Repositories;

use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Support\Collection;

interface StorefrontOrderRepositoryContract
{
    public function recent(User $user): Collection;

    public function find(User $user, int $orderId): ?Order;

    public function tracked(User $user, string $requestedOrder): ?Order;

    public function latest(User $user): ?Order;
}
