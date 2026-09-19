<?php

namespace Tests\Feature;

use App\Contracts\Auth\FirebaseIdTokenContract;
use App\Contracts\Auth\OtpContract;
use App\Contracts\Auth\PhoneCredentialContract;
use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\InvalidPhoneCredentialException;
use App\Services\Auth\PhoneCredentialService;
use App\Support\MobileNumber;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Covers the decisions that keep phone sign-in safe, without touching the
 * database: which credential is accepted, and which number comes out.
 */
class FirebasePhoneSignInTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('erp_auth.otp.bypass_numbers', ['9000000003', '9000000004']);
        config()->set('erp_auth.otp.bypass_code', '123456');
    }

    public function test_the_verifiers_resolve_from_the_container(): void
    {
        $this->assertInstanceOf(FirebaseIdTokenContract::class, app(FirebaseIdTokenContract::class));
        $this->assertInstanceOf(PhoneCredentialContract::class, app(PhoneCredentialContract::class));
    }

    public function test_the_firebase_routes_are_registered_for_both_apps(): void
    {
        $paths = collect(Route::getRoutes())->map(fn ($route): string => $route->uri())->all();

        $this->assertContains('api/v1/auth/customer/firebase/verify', $paths);
        $this->assertContains('api/v1/auth/dealer/firebase/verify', $paths);
    }

    public function test_an_id_token_wins_and_the_posted_mobile_is_ignored(): void
    {
        $verified = $this->service()->verify([
            'id_token' => 'a-token',
            'mobile' => '9111111111',
        ], 'customer_login');

        $this->assertSame('9876543210', $verified->mobile);
        $this->assertSame(VerifiedPhone::SOURCE_FIREBASE, $verified->source);
    }

    public function test_a_listed_bypass_number_may_use_the_test_code(): void
    {
        $verified = $this->service()->verify([
            'mobile' => '9000000003',
            'otp' => '123456',
        ], 'customer_login');

        $this->assertSame('9000000003', $verified->mobile);
        $this->assertSame(VerifiedPhone::SOURCE_BYPASS, $verified->source);
    }

    public function test_an_unlisted_number_cannot_use_the_bypass(): void
    {
        $this->expectException(InvalidPhoneCredentialException::class);

        $this->service()->verify(['mobile' => '9123456789', 'otp' => '123456'], 'customer_login');
    }

    public function test_a_credential_with_neither_token_nor_bypass_is_refused(): void
    {
        $this->expectException(InvalidPhoneCredentialException::class);

        $this->service()->verify(['mobile' => '9000000003'], 'customer_login');
    }

    public function test_a_rejected_token_is_not_retried_as_a_bypass(): void
    {
        $this->expectException(InvalidPhoneCredentialException::class);

        $this->service(tokenIsValid: false)->verify([
            'id_token' => 'forged',
            'mobile' => '9000000003',
            'otp' => '123456',
        ], 'customer_login');
    }

    public function test_every_spelling_of_a_number_resolves_to_the_same_ten_digits(): void
    {
        $this->assertSame('9876543210', MobileNumber::normalise('+91 98765-43210'));
        $this->assertSame('9876543210', MobileNumber::normalise('919876543210'));
        $this->assertSame('+919876543210', MobileNumber::e164('9876543210'));
        $this->assertTrue(MobileNumber::matches('+919876543210', '9876543210'));
        $this->assertFalse(MobileNumber::matches('', '9876543210'));
        $this->assertSame(
            ['9876543210', '919876543210', '+919876543210'],
            MobileNumber::lookupVariants('+91 98765 43210')
        );
    }

    public function test_a_token_without_a_phone_number_claim_is_refused(): void
    {
        $this->expectException(InvalidPhoneCredentialException::class);

        app(FirebaseIdTokenContract::class)->verify('not-a-jwt');
    }

    private function service(bool $tokenIsValid = true): PhoneCredentialService
    {
        return new PhoneCredentialService(
            $this->fakeFirebase($tokenIsValid),
            $this->fakeOtp(),
        );
    }

    private function fakeFirebase(bool $valid): FirebaseIdTokenContract
    {
        return new class($valid) implements FirebaseIdTokenContract
        {
            public function __construct(private readonly bool $valid) {}

            public function verify(string $idToken): VerifiedPhone
            {
                if (! $this->valid) {
                    throw InvalidPhoneCredentialException::because('Phone verification failed.');
                }

                return VerifiedPhone::fromFirebase('+919876543210', 'firebase-uid-1');
            }
        };
    }

    private function fakeOtp(): OtpContract
    {
        return new class implements OtpContract
        {
            public function issue(string $mobile, string $purpose): array
            {
                return ['code' => '123456', 'debug_code' => '123456'];
            }

            public function verify(string $mobile, string $purpose, string $code): bool
            {
                return $code === '123456';
            }
        };
    }
}
