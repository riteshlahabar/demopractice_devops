<?php

namespace App\Data\Finance;

/**
 * What the gateway says happened, after its signature has been checked.
 *
 * `signatureValid` is carried rather than assumed so a caller cannot act on an
 * unverified response by forgetting to ask.
 */
final readonly class GatewayResult
{
    public function __construct(
        public string $reference,
        public bool $signatureValid,
        public bool $paid,
        public string $responseCode,
        public ?string $gatewayReference = null,
        // What we asked the payer for. Checked against our own record.
        public ?string $transactionAmount = null,
        // What the payer was actually charged, which may include the bank's
        // convenience fee and so is recorded but never used for matching.
        public ?string $totalAmount = null,
        public ?string $paymentMode = null,
        public array $payload = [],
    ) {}

    /**
     * A response is only actionable when the bank signed it. Everything else —
     * including a cheerful "E000" — is just text somebody sent us.
     */
    public function isTrustworthy(): bool
    {
        return $this->signatureValid && $this->reference !== '';
    }
}
