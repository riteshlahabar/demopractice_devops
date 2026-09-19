<?php

namespace App\Contracts\Auth;

use App\Data\Auth\VerifiedPhone;

/**
 * Short-lived proof that a mobile number was already verified, so sign-up can
 * be split into "verify the number" and "fill in the details" without asking
 * for a second OTP — and without trusting a number sent in the request body.
 */
interface RegistrationTokenContract
{
    public const DEALER_REGISTRATION = 'dealer_registration';

    public function issue(VerifiedPhone $phone, string $purpose): string;

    /**
     * The phone the token was issued for, or null when it is invalid, expired,
     * or was issued for a different purpose.
     */
    public function resolve(string $token, string $purpose): ?VerifiedPhone;

    public function lifetimeSeconds(): int;
}
