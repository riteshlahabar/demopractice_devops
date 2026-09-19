<?php

namespace App\Services\Finance\Eazypay;

use App\Exceptions\Finance\PaymentGatewayException;

/**
 * SRP: the AES-128-ECB encoding Eazypay expects on every request parameter.
 *
 * ECB is a poor choice of mode, but it is the bank's choice and the wire
 * format is not ours to pick. It is isolated here so nothing else in the
 * codebase is tempted to reuse it for anything of our own.
 */
final class EazypayCipher
{
    private const CIPHER = 'aes-128-ecb';

    private const KEY_LENGTH = 16;

    public function __construct(private readonly string $key) {}

    public function encrypt(string $value): string
    {
        $this->assertKey();

        $encrypted = openssl_encrypt($value, self::CIPHER, $this->key, OPENSSL_RAW_DATA);

        if ($encrypted === false) {
            throw PaymentGatewayException::because('Payment request could not be prepared.');
        }

        return base64_encode($encrypted);
    }

    private function assertKey(): void
    {
        if (strlen($this->key) !== self::KEY_LENGTH) {
            // Said plainly here because it is a deployment mistake, not
            // something a payer can cause. The key itself is never included.
            throw PaymentGatewayException::because(
                'EAZYPAY_ENCRYPTION_KEY must be exactly 16 characters.'
            );
        }
    }
}
