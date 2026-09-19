<?php

namespace App\Services\Auth;

use App\Contracts\Auth\RegistrationTokenContract;
use App\Data\Auth\VerifiedPhone;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\StringEncrypter;

/**
 * Stateless registration token: the verified mobile, purpose and expiry,
 * encrypted and MAC-signed with the app key, so it cannot be forged or edited
 * and needs no table.
 */
final class EncryptedRegistrationTokenService implements RegistrationTokenContract
{
    private const LIFETIME_MINUTES = 15;

    public function __construct(private readonly StringEncrypter $encrypter) {}

    public function issue(VerifiedPhone $phone, string $purpose): string
    {
        return $this->encrypter->encryptString((string) json_encode([
            'mobile' => $phone->mobile,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES)->getTimestamp(),
        ]));
    }

    public function resolve(string $token, string $purpose): ?VerifiedPhone
    {
        try {
            $payload = json_decode($this->encrypter->decryptString($token), true);
        } catch (DecryptException) {
            return null;
        }

        if (! is_array($payload)
            || ($payload['purpose'] ?? null) !== $purpose
            || (int) ($payload['expires_at'] ?? 0) < now()->getTimestamp()) {
            return null;
        }

        $phone = VerifiedPhone::fromRegistrationToken((string) ($payload['mobile'] ?? ''));

        return $phone->isValid() ? $phone : null;
    }

    public function lifetimeSeconds(): int
    {
        return self::LIFETIME_MINUTES * 60;
    }
}
