<?php

namespace App\Services\Auth;

use App\Contracts\Auth\FirebaseIdTokenContract;
use App\Contracts\Auth\OtpContract;
use App\Contracts\Auth\PhoneCredentialContract;
use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\InvalidPhoneCredentialException;
use App\Support\MobileNumber;

/**
 * Decides which proof of phone ownership a sign-in request carries.
 *
 * Firebase is the only route for real users. The bypass exists because a
 * tester cannot receive a Firebase SMS on a number that is not a real handset,
 * and it is deliberately narrow: the number must be listed in
 * OTP_BYPASS_NUMBERS and the code must be the configured bypass code. Any
 * other number sending a plain code is refused outright, so the presence of
 * this path cannot be used to skip Firebase for an arbitrary number.
 */
final class PhoneCredentialService implements PhoneCredentialContract
{
    public function __construct(
        private readonly FirebaseIdTokenContract $firebase,
        private readonly OtpContract $otp,
    ) {}

    public function verify(array $credential, string $purpose): VerifiedPhone
    {
        $idToken = trim((string) ($credential['id_token'] ?? ''));

        if ($idToken !== '') {
            return $this->firebase->verify($idToken);
        }

        return $this->bypass($credential, $purpose);
    }

    /**
     * @param  array<string, mixed>  $credential
     */
    private function bypass(array $credential, string $purpose): VerifiedPhone
    {
        $mobile = MobileNumber::normalise($credential['mobile'] ?? null);
        $code = trim((string) ($credential['otp'] ?? ''));

        if ($mobile === '' || $code === '' || ! $this->isBypassNumber($mobile)) {
            throw InvalidPhoneCredentialException::because('Phone verification failed. Please sign in again.');
        }

        if (! $this->otp->verify($mobile, $purpose, $code)) {
            throw InvalidPhoneCredentialException::because('Invalid or expired code.');
        }

        return VerifiedPhone::fromBypass($mobile);
    }

    private function isBypassNumber(string $mobile): bool
    {
        $configured = array_map(
            static fn ($number): string => MobileNumber::normalise((string) $number),
            (array) config('erp_auth.otp.bypass_numbers', []),
        );

        return in_array($mobile, array_filter($configured), true);
    }
}
