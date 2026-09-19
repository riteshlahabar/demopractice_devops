<?php

namespace App\Services\Finance\Eazypay;

use Illuminate\Support\Facades\Log;

/**
 * SRP: reproducing the SHA-512 "RS" signature Eazypay returns with a payment
 * response, and comparing it in constant time.
 *
 * This is the only thing standing between a real bank response and a forged
 * one typed into the browser's address bar, so a missing or unreadable
 * signature is a failure, never a pass.
 */
final class EazypaySignature
{
    public function __construct(private readonly string $key) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public function matches(array $response): bool
    {
        $received = trim((string) ($response['RS'] ?? ''));

        if ($received === '' || $this->key === '') {
            Log::warning('Eazypay response rejected: no signature to check.');

            return false;
        }

        $expected = $this->expected($response);

        if (! hash_equals($expected, $received)) {
            // The computed value is logged because reconciling it against the
            // bank's first UAT response is how a field-order mismatch is
            // found. The key is not part of what is logged.
            Log::warning('Eazypay response signature mismatch.', [
                'reference' => $response['ReferenceNo'] ?? null,
                'expected' => $expected,
                'received' => $received,
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function expected(array $response): string
    {
        $fields = (array) config('eazypay.response.signature_fields', []);

        $parts = array_map(
            static fn ($field): string => (string) ($response[$field] ?? ''),
            $fields,
        );

        $parts[] = $this->key;

        return hash('sha512', implode('|', $parts));
    }
}
