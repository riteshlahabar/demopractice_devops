<?php

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * A verified mobile number has no dealer account yet, and the request carried
 * no owner/firm details to create one — login alone cannot register a dealer.
 */
final class DealerNotRegisteredException extends RuntimeException
{
    public static function make(): self
    {
        return new self('This mobile number is not registered as a dealer. Please register your firm first.');
    }
}
