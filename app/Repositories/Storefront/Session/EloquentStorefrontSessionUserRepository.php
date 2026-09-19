<?php

namespace App\Repositories\Storefront\Session;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionUserRepositoryContract;
use App\Models\User;

final class EloquentStorefrontSessionUserRepository implements StorefrontSessionUserRepositoryContract
{
    public function find(int $userId, string $role): ?User
    {
        return User::query()
            ->with(['customerProfile', 'dealerProfile.salesman', 'addresses'])
            ->whereKey($userId)
            ->where('role', $role)
            ->first();
    }
}
