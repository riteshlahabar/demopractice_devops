<?php

namespace App\Contracts\Sales;

use App\Models\Sales\Order;

/**
 * SRP: the single authority on orders.status. Every sales action reports what
 * happened and this decides the status, so no screen sets it by hand.
 */
interface OrderStatusContract
{
    /**
     * Moves the order to $status when that is a step forward. Returns true when
     * the order was actually moved.
     */
    public function moveTo(Order $order, string $status): bool;

    /** Invoice raised for the order. */
    public function invoiceCreated(Order $order): bool;

    /** A dispatch row was created or changed; the order follows its stage. */
    public function dispatchProgressed(Order $order, string $dispatchStatus, bool $dispatched, bool $outForDelivery, bool $delivered): bool;

    /** The one status an admin still sets by hand. */
    public function cancel(Order $order, string $reason, ?int $userId): bool;
}
