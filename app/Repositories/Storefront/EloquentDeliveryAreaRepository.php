<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\DeliveryAreaRepositoryContract;
use App\Models\Storefront\DeliveryArea;

final class EloquentDeliveryAreaRepository implements DeliveryAreaRepositoryContract
{
    public function active(): array
    {
        return DeliveryArea::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('district_name')
            ->get(['district_code', 'district_name', 'state_name', 'min_order_amount'])
            ->map(fn (DeliveryArea $area): array => [
                'code' => $area->district_code,
                'name' => $area->district_name,
                'state' => $area->state_name,
                'min_order' => $area->min_order_amount !== null ? (float) $area->min_order_amount : null,
            ])
            ->all();
    }
}
