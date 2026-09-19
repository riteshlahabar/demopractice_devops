<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\OrderStatusContract;
use App\Models\Sales\Order;

final class OrderStatusService implements OrderStatusContract
{
    /** Statuses in the order they happen; position decides what counts as forward. */
    public const FLOW = [
        'salesman_review',
        'admin_review',
        'approved',
        'packing',
        'dispatched',
        'out_for_delivery',
        'delivered',
    ];

    public const CANCELLED = 'cancelled';

    public function moveTo(Order $order, string $status): bool
    {
        if (! in_array($status, self::FLOW, true) || ! $this->isForward((string) $order->status, $status)) {
            return false;
        }

        $updates = ['status' => $status];

        if ($status === 'approved' && $order->approved_at === null) {
            $updates += ['approved_by' => auth()->id(), 'approved_at' => now()];
        }

        $order->forceFill($updates)->save();

        return true;
    }

    public function invoiceCreated(Order $order): bool
    {
        return $this->moveTo($order, 'approved');
    }

    public function dispatchProgressed(Order $order, string $dispatchStatus, bool $dispatched, bool $outForDelivery, bool $delivered): bool
    {
        return $this->moveTo($order, $this->dispatchStage($dispatchStatus, $dispatched, $outForDelivery, $delivered));
    }

    public function cancel(Order $order, string $reason, ?int $userId): bool
    {
        if (in_array((string) $order->status, [self::CANCELLED, 'delivered'], true)) {
            return false;
        }

        $order->forceFill([
            'status' => self::CANCELLED,
            'cancel_reason' => trim($reason),
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
        ])->save();

        return true;
    }

    /** The order stage a dispatch row stands for. */
    private function dispatchStage(string $dispatchStatus, bool $dispatched, bool $outForDelivery, bool $delivered): string
    {
        if ($delivered || $dispatchStatus === 'delivered') {
            return 'delivered';
        }

        if ($outForDelivery || $dispatchStatus === 'out_for_delivery') {
            return 'out_for_delivery';
        }

        if ($dispatched || in_array($dispatchStatus, ['dispatched', 'in_transit'], true)) {
            return 'dispatched';
        }

        return 'packing';
    }

    /** A cancelled order never moves again, and the flow never runs backwards. */
    private function isForward(string $current, string $next): bool
    {
        if ($current === self::CANCELLED) {
            return false;
        }

        $currentIndex = array_search($current, self::FLOW, true);
        $nextIndex = array_search($next, self::FLOW, true);

        return $nextIndex !== false && ($currentIndex === false || $nextIndex > $currentIndex);
    }
}
