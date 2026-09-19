<?php

namespace App\Contracts\Finance;

use App\Data\Finance\GatewayResult;

/**
 * SRP: talking to one hosted payment gateway.
 *
 * An interface because the gateway is the part most likely to be replaced —
 * and because a fake implementation is the only sane way to test a checkout
 * without sending money to a bank.
 */
interface PaymentGatewayContract
{
    /**
     * False when the gateway is switched off or not configured, so a caller
     * can offer another payment method instead of failing at the redirect.
     */
    public function isAvailable(): bool;

    /**
     * The URL the payer is sent to. `$amount` is a plain decimal string so no
     * float rounding can creep between our total and the bank's.
     */
    public function redirectUrl(string $reference, string $amount): string;

    /**
     * @param  array<string, mixed>  $response  the raw callback parameters
     */
    public function parse(array $response): GatewayResult;
}
