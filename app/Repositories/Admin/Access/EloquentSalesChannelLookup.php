<?php

namespace App\Repositories\Admin\Access;

use App\Contracts\Admin\Access\SalesChannelLookupContract;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * Reads the channel through each sales module's `channel` config
 * (orders.order_type directly, the others through their order).
 */
final class EloquentSalesChannelLookup implements SalesChannelLookupContract
{
    public function forRecords(string $module, array $ids): array
    {
        $config = config('admin.modules.'.$module);
        if (! is_array($config) || empty($config['channel']['column']) || $ids === []) {
            return [];
        }

        $relation = $config['channel']['relation'] ?? null;
        $column = (string) $config['channel']['column'];
        $model = $config['model'];

        $query = $model::query()->whereKey(array_values(array_unique($ids)));
        if ($relation) {
            $query->with($relation);
        }

        return $query->get()->mapWithKeys(fn (Model $record): array => [
            $record->getKey() => ($value = data_get($record, $relation ? $relation.'.'.$column : $column)) ? (string) $value : null,
        ])->all();
    }

    public function forOrder(int|string $orderId): ?string
    {
        $channel = Order::query()->whereKey($orderId)->value('order_type');

        return $channel ? (string) $channel : null;
    }
}
