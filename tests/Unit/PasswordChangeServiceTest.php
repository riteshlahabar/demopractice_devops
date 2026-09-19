<?php

namespace Tests\Unit;

use App\Contracts\Auth\PasswordChangeContract;
use App\Models\User;
use App\Services\Auth\PasswordChangeService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PasswordChangeServiceTest extends TestCase
{
    private PasswordChangeContract $passwords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwords = $this->app->make(PasswordChangeContract::class);
    }

    public function test_contract_is_bound_to_the_service(): void
    {
        $this->assertInstanceOf(PasswordChangeService::class, $this->passwords);
    }

    public function test_otp_account_has_no_password_of_its_own(): void
    {
        $user = new User(['email' => '9876543210@customer.bawaskar.local', 'password' => 'random-hidden-password']);

        $this->assertFalse($this->passwords->hasPassword($user));
        $this->assertTrue($this->passwords->currentPasswordMatches($user, null));
    }

    public function test_otp_account_that_set_a_password_must_confirm_it(): void
    {
        $user = new User(['email' => '9876543210@dealer.bawaskar.local', 'password' => 'chosen-password']);
        $user->password_set_at = now();

        $this->assertTrue($this->passwords->hasPassword($user));
        $this->assertFalse($this->passwords->currentPasswordMatches($user, null));
        $this->assertTrue($this->passwords->currentPasswordMatches($user, 'chosen-password'));
    }

    public function test_email_account_needs_the_correct_current_password(): void
    {
        $user = new User(['email' => 'dealer@example.com', 'password' => 'secret-pass']);

        $this->assertTrue($this->passwords->hasPassword($user));
        $this->assertFalse($this->passwords->currentPasswordMatches($user, ''));
        $this->assertFalse($this->passwords->currentPasswordMatches($user, 'wrong-pass'));
        $this->assertTrue($this->passwords->currentPasswordMatches($user, 'secret-pass'));
    }

    public function test_change_password_routes_are_registered_for_both_apps(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => implode('|', $route->methods()).' '.$route->uri())
            ->all();

        foreach (['customer', 'dealer'] as $prefix) {
            $this->assertContains("GET|HEAD api/v1/{$prefix}/change-password", $routes);
            $this->assertContains("POST api/v1/{$prefix}/change-password", $routes);
        }
    }
}
