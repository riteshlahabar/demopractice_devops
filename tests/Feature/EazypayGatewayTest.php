<?php

namespace Tests\Feature;

use App\Contracts\Finance\PaymentGatewayContract;
use App\Exceptions\Finance\PaymentGatewayException;
use App\Services\Finance\Eazypay\EazypayCipher;
use App\Services\Finance\Eazypay\EazypayGateway;
use App\Services\Finance\Eazypay\EazypaySignature;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Covers the two things that decide whether money is recorded correctly: what
 * we send the bank, and whether we believe what comes back.
 */
class EazypayGatewayTest extends TestCase
{
    private const KEY = '1234567890123456';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('eazypay.enabled', true);
        config()->set('eazypay.merchant_id', '100011');
        config()->set('eazypay.encryption_key', self::KEY);
        config()->set('eazypay.sub_merchant_id', '45');
        config()->set('eazypay.paymode', '9');
        config()->set('eazypay.base_url', 'https://eazypay.icicibank.com/EazyPG');
        config()->set('eazypay.return_url', 'https://example.test/payments/eazypay/callback');
    }

    public function test_the_gateway_resolves_from_the_container(): void
    {
        $this->assertInstanceOf(PaymentGatewayContract::class, app(PaymentGatewayContract::class));
    }

    public function test_the_callback_route_is_registered_and_accepts_a_post(): void
    {
        $methods = collect(Route::getRoutes())
            ->first(fn ($route): bool => $route->uri() === 'payments/eazypay/callback')
            ?->methods() ?? [];

        $this->assertContains('POST', $methods);
        $this->assertContains('GET', $methods);
    }

    public function test_the_redirect_url_carries_the_merchant_id_in_the_clear_and_everything_else_encrypted(): void
    {
        $url = $this->gateway()->redirectUrl('PAY260910ABCD1234', '1500.00');

        // parse_str() would turn 'mandatory fields' into 'mandatory_fields',
        // so the query is split by hand to see the real parameter names.
        $query = $this->queryOf($url);

        $this->assertSame('100011', $query['merchantid']);
        $this->assertSame(
            'PAY260910ABCD1234|45|1500.00',
            $this->decrypt($query['mandatory fields'])
        );
        $this->assertSame('1500.00', $this->decrypt($query['transaction amount']));
        $this->assertSame('PAY260910ABCD1234', $this->decrypt($query['Reference No']));
        $this->assertSame('https://example.test/payments/eazypay/callback', $this->decrypt($query['returnurl']));

        // Base64 contains "+" and "/", which must survive the query string.
        $this->assertStringNotContainsString(' ', (string) parse_url($url, PHP_URL_QUERY));
    }

    public function test_a_short_key_is_refused_rather_than_silently_encrypting_wrong(): void
    {
        config()->set('eazypay.encryption_key', 'too-short');

        $this->expectException(PaymentGatewayException::class);

        $this->gateway('too-short')->redirectUrl('PAY1', '10.00');
    }

    public function test_the_gateway_reports_itself_unavailable_when_not_configured(): void
    {
        config()->set('eazypay.merchant_id', '');

        $this->assertFalse($this->gateway()->isAvailable());
    }

    public function test_a_correctly_signed_success_is_accepted(): void
    {
        $result = $this->gateway()->parse($this->signedResponse());

        $this->assertTrue($result->signatureValid);
        $this->assertTrue($result->paid);
        $this->assertTrue($result->isTrustworthy());
        $this->assertSame('PAY260910ABCD1234', $result->reference);
        $this->assertSame('1500.00', $result->transactionAmount);
        $this->assertSame('1518.00', $result->totalAmount);
    }

    public function test_a_tampered_amount_breaks_the_signature(): void
    {
        $response = $this->signedResponse();
        $response['Transaction Amount'] = '1.00';

        $result = $this->gateway()->parse($response);

        $this->assertFalse($result->signatureValid);
        $this->assertFalse($result->isTrustworthy());
    }

    public function test_a_response_with_no_signature_is_not_trusted(): void
    {
        $response = $this->signedResponse();
        unset($response['RS']);

        $this->assertFalse($this->gateway()->parse($response)->isTrustworthy());
    }

    public function test_a_signed_failure_is_trusted_but_not_paid(): void
    {
        $result = $this->gateway()->parse($this->signedResponse(['Response Code' => 'E001']));

        $this->assertTrue($result->signatureValid);
        $this->assertFalse($result->paid);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function signedResponse(array $overrides = []): array
    {
        $response = array_merge([
            'ID' => '100011',
            'Response Code' => 'E000',
            'Unique Ref Number' => '9988776655',
            'Service Tax Amount' => '0.00',
            'Processing Fee Amount' => '18.00',
            'Total Amount' => '1518.00',
            'Transaction Amount' => '1500.00',
            'Transaction Date' => '10-09-2026 12:30:00',
            'Interchange Value' => '',
            'TDR' => '',
            'Payment Mode' => 'UPI',
            'SubMerchantId' => '45',
            'ReferenceNo' => 'PAY260910ABCD1234',
            'TPS' => 'Y',
        ], $overrides);

        $response['RS'] = (new EazypaySignature(self::KEY))->expected($response);

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function queryOf(string $url): array
    {
        $pairs = explode('&', (string) parse_url($url, PHP_URL_QUERY));
        $query = [];

        foreach ($pairs as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $query[urldecode($key)] = urldecode($value);
        }

        return $query;
    }

    private function gateway(string $key = self::KEY): EazypayGateway
    {
        return new EazypayGateway(new EazypayCipher($key), new EazypaySignature($key));
    }

    private function decrypt(string $value): string
    {
        $plain = openssl_decrypt(base64_decode($value), 'aes-128-ecb', self::KEY, OPENSSL_RAW_DATA);

        return (string) $plain;
    }
}
