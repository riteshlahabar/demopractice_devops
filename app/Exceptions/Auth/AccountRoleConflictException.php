<?php

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * A mobile number already belongs to a different kind of account.
 *
 * One number is one identity across the whole system, so a dealer number
 * cannot quietly become a customer login — the caller is told plainly instead.
 */
final class AccountRoleConflictException extends RuntimeException
{
    public static function forRole(string $role): self
    {
        return new self("This mobile number is registered for another account type ({$role}).");
    }
}
