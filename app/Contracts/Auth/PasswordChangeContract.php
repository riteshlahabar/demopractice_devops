<?php

namespace App\Contracts\Auth;

use App\Models\User;

/**
 * Changing the password of a signed-in app user (customer, dealer, salesman).
 */
interface PasswordChangeContract
{
    /**
     * False for accounts created by mobile OTP that never chose a password —
     * they were given a random one, so they cannot be asked for it.
     */
    public function hasPassword(User $user): bool;

    /**
     * Always true when the account has no password of its own yet.
     */
    public function currentPasswordMatches(User $user, ?string $currentPassword): bool;

    /**
     * Saves the new password and signs out every other device. The token in
     * $keepToken (the caller's own bearer token) stays valid.
     */
    public function change(User $user, string $newPassword, ?string $keepToken = null): void;
}
