<?php

namespace App\Observers\Notifications;

use App\Models\DealerProfile;
use App\Models\Sales\Order;
use App\Models\User;

/**
 * Customer/dealer: order placed and every later status change.
 * Salesman: a dealer's own order is waiting for their review.
 */
final class OrderNotificationObserver extends NotificationObserver
{
    public function created(Order $order): void
    {
        $this->notify($this->ownerId($order), 'order', 'placed', $this->replace($order), $this->data($order));

        // A salesman who placed the order does not need telling about it.
        if ($order->status === 'salesman_review' && ! $order->salesman_id && $order->dealer_id) {
            $this->attempt(function () use ($order): void {
                $salesmanId = DealerProfile::query()->where('user_id', $order->dealer_id)->value('salesman_id');
                $dealer = User::query()->with('dealerProfile')->find($order->dealer_id);
                $name = $dealer?->dealerProfile?->firm_name ?: ($dealer?->name ?: 'A dealer');

                $this->notify($salesmanId ? (int) $salesmanId : null, 'order_review', 'salesman_review', $this->replace($order) + ['dealer' => $name], $this->data($order));
            });
        }
    }

    public function updated(Order $order): void
    {
        if ($this->statusChanged($order)) {
            $this->notify($this->ownerId($order), 'order', (string) $order->status, $this->replace($order), $this->data($order));
        }
    }

    private function ownerId(Order $order): ?int
    {
        $id = $order->dealer_id ?: $order->customer_id;

        return $id ? (int) $id : null;
    }

    /**
     * @return array<string, scalar|null>
     */
    private function replace(Order $order): array
    {
        return ['order_no' => $order->order_no, 'amount' => $this->money($order->grand_total)];
    }

    /**
     * @return array<string, scalar|null>
     */
    private function data(Order $order): array
    {
        return ['order_id' => $order->getKey(), 'order_no' => $order->order_no];
    }
}
