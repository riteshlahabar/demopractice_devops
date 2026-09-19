<?php

namespace App\Services\Finance\Eazypay;

use App\Contracts\Finance\PaymentGatewayContract;
use App\Data\Finance\GatewayResult;
use App\Exceptions\Finance\PaymentGatewayException;

/**
 * ICICI Bank Eazypay, hosted redirect flow.
 *
 * Every request parameter but the merchant id is AES encrypted and base64
 * encoded; the parameter names really do contain spaces, which is the bank's
 * spelling and not a typo.
 */
final class EazypayGateway implements PaymentGatewayContract
{
    public function __construct(
        private readonly EazypayCipher $cipher,
        private readonly EazypaySignature $signature,
    ) {}

    public function isAvailable(): bool
    {
        return (bool) config('eazypay.enabled')
            && $this->setting('merchant_id') !== ''
            && $this->setting('encryption_key') !== ''
            && $this->setting('return_url') !== '';
    }

    public function redirectUrl(string $reference, string $amount): string
    {
        if (! $this->isAvailable()) {
            throw PaymentGatewayException::because('Online payment is not available right now.');
        }

        $subMerchantId = $this->setting('sub_merchant_id');

        $parameters = [
            'merchantid' => $this->setting('merchant_id'),
            'mandatory fields' => $this->cipher->encrypt("{$reference}|{$subMerchantId}|{$amount}"),
            'optional fields' => '',
            'returnurl' => $this->cipher->encrypt($this->setting('return_url')),
            'Reference No' => $this->cipher->encrypt($reference),
            'submerchantid' => $this->cipher->encrypt($subMerchantId),
            'transaction amount' => $this->cipher->encrypt($amount),
            'paymode' => $this->cipher->encrypt($this->setting('paymode')),
        ];

        // The encrypted values are base64, which contains "+", "/" and "=";
        // without encoding them a "+" would arrive at the bank as a space and
        // the request would fail to decrypt.
        // RFC3986 so a space in a parameter name becomes %20 and a '+' inside
        // the base64 becomes %2B. With the default '+'-for-space encoding the
        // two are indistinguishable and the bank cannot decrypt the value.
        return rtrim((string) config('eazypay.base_url'), '?').'?'
            .http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    public function parse(array $response): GatewayResult
    {
        $responseCode = trim((string) ($response['Response Code'] ?? ''));

        $successCodes = (array) config('eazypay.response.success_codes', ['E000']);

        return new GatewayResult(
            reference: trim((string) ($response['ReferenceNo'] ?? '')),
            signatureValid: $this->signature->matches($response),
            paid: in_array($responseCode, $successCodes, true),
            responseCode: $responseCode,
            gatewayReference: $this->value($response, 'Unique Ref Number'),
            transactionAmount: $this->value($response, 'Transaction Amount'),
            totalAmount: $this->value($response, 'Total Amount'),
            paymentMode: $this->value($response, 'Payment Mode'),
            payload: $response,
        );
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function value(array $response, string $key): ?string
    {
        $value = trim((string) ($response[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    private function setting(string $key): string
    {
        return trim((string) config("eazypay.{$key}", ''));
    }
}
