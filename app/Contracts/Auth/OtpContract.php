<?php

namespace App\Contracts\Auth;

/**
 * SRP: issuing and checking the one time codes used for mobile login.
 *
 * Kept out of the controllers because the rules here are security sensitive:
 * outside production the code is fixed, some numbers bypass it entirely, and a
 * code is burnt after a few wrong guesses.
 */
interface OtpContract
{
    /**
     * Issues a code and stores its hash.
     *
     * @return array{code: string, debug_code: string|null} debug_code is null
     *                                                      in production.
     */
    public function issue(string $mobile, string $purpose): array;

    public function verify(string $mobile, string $purpose, string $code): bool;
}
