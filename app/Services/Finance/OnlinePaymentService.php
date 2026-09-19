<?php

namespace App\Services\Finance;

use App\Contracts\Finance\PaymentGatewayContract;
use App\Data\Finance\GatewayResult;
use App\Exceptions\Finance\PaymentGatewayException;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SRP: the life of one online payment attempt — open it, then settle it.
 *
 * The gateway knows how to talk to the bank and nothing about our orders; this
 * knows about our orders and nothing about AES or signatures.
 */
final class OnlinePaymentService
{
    public const GATEWAY = 'eazypay';

    public function __construct(private readonly PaymentGatewayContract $gateway) {}

    /**
     * Opens a pending payment and returns where to send the payer.
     *
     * A fresh row is created per attempt rather than reused, so an abandoned
     * attempt cannot be settled later by a replayed callback.
     */
    public function start(Order $order, User $payer): array
    {
        if (! $this->gateway->isAvailable()) {
            throw PaymentGatewayException::because('Online payment is not available right now.');
        }

        if ($order->payment_status === 'paid') {
            throw PaymentGatewayException::because('This order is already paid.');
        }

        $amount = $this->decimal($order->grand_total);

        if ((float) $amount <= 0) {
            throw PaymentGatewayException::because('This order has nothing to pay.');
        }

        $payment = Payment::query()->create([
            'payment_no' => $this->reference(),
            'order_id' => $order->id,
            'payer_id' => $payer->id,
            'payment_mode' => 'online',
            'gateway' => self::GATEWAY,
            'status' => 'pending',
            'amount' => $amount,
        ]);

        return [
            'payment_no' => $payment->payment_no,
            'amount' => $amount,
            'redirect_url' => $this->gateway->redirectUrl($payment->payment_no, $amount),
        ];
    }

    /**
     * Applies a callback. Refuses anything the bank did not sign, and is safe
     * to call twice: the payer's browser can replay the return URL, and a
     * second call must not mark a second payment or credit an order twice.
     */
    public function settle(GatewayResult $result): ?Payment
    {
        if (! $result->isTrustworthy()) {
            Log::warning('Payment callback refused: signature not valid.', ['reference' => $result->reference]);

            return null;
        }

        return DB::transaction(function () use ($result): ?Payment {
            $payment = Payment::query()
                ->where('payment_no', $result->reference)
                ->where('gateway', self::GATEWAY)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::warning('Payment callback refers to no known attempt.', ['reference' => $result->reference]);

                return null;
            }

            if ($payment->status !== 'pending') {
                return $payment;
            }

            return $result->paid
                ? $this->markPaid($payment, $result)
                : $this->markFailed($payment, $result, "Gateway response code {$result->responseCode}.");
        });
    }

    private function markPaid(Payment $payment, GatewayResult $result): Payment
    {
        // The amount is checked against our own record rather than trusted, so
        // a signed response for a smaller sum cannot close a larger order.
        if (! $this->amountMatches($payment, $result)) {
            Log::warning('Payment callback amount does not match the attempt.', [
                'reference' => $result->reference,
                'expected' => $this->decimal($payment->amount),
                'received' => $result->transactionAmount,
            ]);

            return $this->markFailed($payment, $result, 'Amount did not match the order.');
        }

        $payment->forceFill([
            'status' => 'success',
            'payment_mode' => $result->paymentMode ?: 'online',
            'transaction_ref' => $result->gatewayReference,
            'gateway_payload' => $result->payload,
            'failure_reason' => null,
            'paid_at' => now(),
        ])->save();

        $payment->order?->forceFill([
            'payment_status' => 'paid',
            'payment_method' => 'online',
        ])->save();

        return $payment;
    }

    private function markFailed(Payment $payment, GatewayResult $result, string $reason): Payment
    {
        $payment->forceFill([
            'status' => 'failed',
            'transaction_ref' => $result->gatewayReference,
            'gateway_payload' => $result->payload,
            'failure_reason' => $reason,
        ])->save();

        return $payment;
    }

    private function amountMatches(Payment $payment, GatewayResult $result): bool
    {
        if ($result->transactionAmount === null) {
            return false;
        }

        return $this->decimal($payment->amount) === $this->decimal($result->transactionAmount);
    }

    private function decimal(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * The bank keys the transaction on this, so it has to be unique for the
     * life of the merchant account, not just for this order.
     */
    private function reference(): string
    {
        do {
            $reference = 'PAY'.now()->format('ymd').Str::upper(Str::random(8));
        } while (Payment::query()->where('payment_no', $reference)->exists());

        return $reference;
    }
}
