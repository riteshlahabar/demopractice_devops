<?php

namespace App\Observers\Sales;

use App\Contracts\Sales\OrderStatusContract;
use App\Models\Sales\Dispatch;
use Throwable;

/**
 * A dispatch row is the packing/delivery record, so the order follows it:
 * created = Packing, dispatched = Dispatched, out for delivery, delivered.
 */
final class DispatchOrderStatusObserver
{
    public function __construct(
        private readonly OrderStatusContract $status
    ) {}

    public function created(Dispatch $dispatch): void
    {
        $this->sync($dispatch);
    }

    public function updated(Dispatch $dispatch): void
    {
        $this->sync($dispatch);
    }

    /** Never let a status update break saving the dispatch itself. */
    private function sync(Dispatch $dispatch): void
    {
        try {
            $order = $dispatch->order;

            if ($order === null) {
                return;
            }

            $this->status->dispatchProgressed(
                $order,
                (string) $dispatch->status,
                $dispatch->dispatched_at !== null,
                $dispatch->out_for_delivery_at !== null,
                $dispatch->delivered_at !== null,
            );
        } catch (Throwable) {
            // Status will catch up on the next save.
        }
    }
}
