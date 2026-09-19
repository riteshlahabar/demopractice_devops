<?php

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * Raised when a phone credential cannot be trusted — a bad Firebase ID token,
 * a token for another project, an expired one, or a bypass attempt from a
 * number that is not on the bypass list.
 *
 * The message is deliberately vague: telling a caller which check failed helps
 * nobody but an attacker. The detail goes to the log instead.
 */
final class InvalidPhoneCredentialException extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
