<?php

namespace App\Contracts\Storefront\Session\Repositories;

use App\Models\User;

interface StorefrontSessionUserRepositoryContract
{
    public function find(int $userId, string $role): ?User;
}
