<?php

namespace App\Data\Auth;

use App\Support\MobileNumber;

/**
 * A mobile number this server has proved ownership of.
 *
 * The constructor is private on purpose: only a verifier may name a number
 * verified, which is what stops a controller from trusting a number that
 * merely arrived in the request body.
 */
final readonly class VerifiedPhone
{
    public const SOURCE_FIREBASE = 'firebase';

    public const SOURCE_BYPASS = 'bypass';

    public const SOURCE_OTP = 'otp';

    public const SOURCE_REGISTRATION = 'registration';

    private function __construct(
        public string $mobile,
        public string $e164,
        public string $source,
        public ?string $providerUid = null,
    ) {}

    public static function fromFirebase(string $phoneNumber, string $uid): self
    {
        return new self(
            MobileNumber::normalise($phoneNumber),
            MobileNumber::e164($phoneNumber),
            self::SOURCE_FIREBASE,
            $uid,
        );
    }

    /**
     * Only the numbers listed in OTP_BYPASS_NUMBERS reach this, and only with
     * the bypass code, so it never widens who can sign in.
     */
    public static function fromBypass(string $mobile): self
    {
        return new self(
            MobileNumber::normalise($mobile),
            MobileNumber::e164($mobile),
            self::SOURCE_BYPASS,
        );
    }

    /**
     * The legacy server-side OTP flow, kept while the apps migrate to Firebase.
     */
    public static function fromOtp(string $mobile): self
    {
        return new self(
            MobileNumber::normalise($mobile),
            MobileNumber::e164($mobile),
            self::SOURCE_OTP,
        );
    }

    /**
     * A number verified moments earlier, carried by a signed registration
     * token. Only the token service reaches this, after checking the token.
     */
    public static function fromRegistrationToken(string $mobile): self
    {
        return new self(
            MobileNumber::normalise($mobile),
            MobileNumber::e164($mobile),
            self::SOURCE_REGISTRATION,
        );
    }

    public function isValid(): bool
    {
        return strlen($this->mobile) === 10;
    }
}
