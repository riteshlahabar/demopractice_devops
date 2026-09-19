<?php

namespace App\Exceptions\Finance;

use RuntimeException;

/**
 * The payment gateway could not be used — misconfigured, or asked for
 * something it cannot do. Never raised for a payment that merely failed;
 * a declined card is a normal result, not an exception.
 */
final class PaymentGatewayException extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
