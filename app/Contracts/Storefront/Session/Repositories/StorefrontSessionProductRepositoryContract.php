<?php

namespace App\Contracts\Storefront\Session\Repositories;

use Illuminate\Support\Collection;

interface StorefrontSessionProductRepositoryContract
{
    public function visibleByIds(string $audience, array $productIds): Collection;
}
