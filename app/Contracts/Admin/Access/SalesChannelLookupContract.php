<?php

namespace App\Contracts\Admin\Access;

/**
 * Whether sales records belong to the customer or the dealer channel.
 */
interface SalesChannelLookupContract
{
    /**
     * @param  list<int|string>  $ids
     * @return array<int|string, string|null> channel per id found
     */
    public function forRecords(string $module, array $ids): array;

    public function forOrder(int|string $orderId): ?string;
}
