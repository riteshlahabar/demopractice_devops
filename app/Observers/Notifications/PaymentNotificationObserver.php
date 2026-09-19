<?php

namespace App\Observers\Notifications;

use App\Models\Finance\Payment;

/**
 * A payment can be created already settled (salesman collection) or move to
 * paid later (online gateway callback), so both events are watched. Pending
 * has no template and sends nothing.
 */
final class PaymentNotificationObserver extends NotificationObserver
{
    public function created(Payment $payment): void
    {
        $this->send($payment);
    }

    public function updated(Payment $payment): void
    {
        if ($this->statusChanged($payment)) {
            $this->send($payment);
        }
    }

    private function send(Payment $payment): void
    {
        $this->notify(
            $payment->payer_id ? (int) $payment->payer_id : null,
            'payment',
            (string) $payment->status,
            ['payment_no' => $payment->payment_no, 'amount' => $this->money($payment->amount)],
            ['payment_id' => $payment->getKey(), 'order_id' => $payment->order_id],
        );
    }
}
