<?php

namespace App\Observers\Notifications;

use App\Models\Sales\ReturnRequest;

final class ReturnRequestNotificationObserver extends NotificationObserver
{
    public function updated(ReturnRequest $return): void
    {
        if (! $this->statusChanged($return)) {
            return;
        }

        $this->notify(
            $return->user_id ? (int) $return->user_id : null,
            'return',
            (string) $return->status,
            ['return_no' => $return->return_no, 'amount' => $this->money($return->refund_amount)],
            ['return_id' => $return->getKey(), 'order_id' => $return->order_id],
        );
    }
}
