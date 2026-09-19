<?php

namespace App\Contracts\Storefront\Repositories;

interface DeliveryAreaRepositoryContract
{
    /**
     * Active delivery areas in display order.
     *
     * @return array<int, array{code: int, name: string, state: string, min_order: float|null}>
     */
    public function active(): array;
}
