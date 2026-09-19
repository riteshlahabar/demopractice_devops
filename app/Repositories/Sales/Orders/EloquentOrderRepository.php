<?php

namespace App\Repositories\Sales\Orders;

use App\Contracts\Sales\Orders\OrderRepositoryContract;
use App\Models\Sales\Order;

final class EloquentOrderRepository implements OrderRepositoryContract
{
    public function create(array $attributes): Order
    {
        return Order::query()->create($attributes);
    }

    public function addItems(Order $order, array $lineItems): array
    {
        $subtotal = 0.0;
        $gstTotal = 0.0;

        foreach ($lineItems as $lineItem) {
            $orderItemData = $lineItem;
            unset($orderItemData['line_base']);

            $order->items()->create($orderItemData);

            $subtotal += (float) $lineItem['line_base'];
            $gstTotal += (float) $lineItem['gst_amount'];
        }

        return [
            'subtotal' => $subtotal,
            'gst_total' => $gstTotal,
        ];
    }

    public function updateTotals(
        Order $order,
        float $subtotal,
        float $gstTotal
    ): void {
        $order->forceFill([
            'subtotal' => $subtotal,
            'gst_total' => $gstTotal,
            'grand_total' => $subtotal + $gstTotal,
        ])->save();
    }

    public function loadForResult(Order $order): Order
    {
        return $order->load(
            'items.product',
            'items.variant',
            'dealer.dealerProfile',
            'salesman',
            'customer'
        );
    }
}
