<?php

namespace Tests\Unit;

use App\Contracts\Auth\RegistrationTokenContract;
use App\Data\Auth\VerifiedPhone;
use App\Services\Auth\EncryptedRegistrationTokenService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegistrationTokenServiceTest extends TestCase
{
    private RegistrationTokenContract $tokens;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokens = $this->app->make(RegistrationTokenContract::class);
    }

    public function test_contract_is_bound_to_the_encrypted_service(): void
    {
        $this->assertInstanceOf(EncryptedRegistrationTokenService::class, $this->tokens);
    }

    public function test_token_resolves_to_the_verified_mobile(): void
    {
        $token = $this->tokens->issue(VerifiedPhone::fromOtp('9876543210'), RegistrationTokenContract::DEALER_REGISTRATION);

        $phone = $this->tokens->resolve($token, RegistrationTokenContract::DEALER_REGISTRATION);

        $this->assertNotNull($phone);
        $this->assertSame('9876543210', $phone->mobile);
        $this->assertSame(VerifiedPhone::SOURCE_REGISTRATION, $phone->source);
    }

    public function test_token_for_another_purpose_is_rejected(): void
    {
        $token = $this->tokens->issue(VerifiedPhone::fromOtp('9876543210'), 'customer_registration');

        $this->assertNull($this->tokens->resolve($token, RegistrationTokenContract::DEALER_REGISTRATION));
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = $this->tokens->issue(VerifiedPhone::fromOtp('9876543210'), RegistrationTokenContract::DEALER_REGISTRATION);

        $this->travel(16)->minutes();

        $this->assertNull($this->tokens->resolve($token, RegistrationTokenContract::DEALER_REGISTRATION));
    }

    public function test_forged_token_is_rejected(): void
    {
        $this->assertNull($this->tokens->resolve('not-a-real-token', RegistrationTokenContract::DEALER_REGISTRATION));
    }

    public function test_dealer_register_route_is_registered(): void
    {
        $paths = collect(Route::getRoutes()->getRoutes())->map->uri()->all();

        $this->assertContains('api/v1/auth/dealer/register', $paths);
    }
}
