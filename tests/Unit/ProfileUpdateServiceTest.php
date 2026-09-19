<?php

namespace Tests\Unit;

use App\Contracts\Account\ProfileUpdateContract;
use App\Models\User;
use App\Services\Account\ProfileUpdateService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProfileUpdateServiceTest extends TestCase
{
    private ProfileUpdateContract $profiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profiles = $this->app->make(ProfileUpdateContract::class);
    }

    public function test_contract_is_bound_to_the_service(): void
    {
        $this->assertInstanceOf(ProfileUpdateService::class, $this->profiles);
    }

    public function test_dealer_edits_firm_details_but_never_mobile(): void
    {
        $rules = $this->profiles->rules(new User(['role' => User::ROLE_DEALER]));

        $this->assertArrayHasKey('firm_name', $rules);
        $this->assertArrayHasKey('gst_number', $rules);
        $this->assertArrayNotHasKey('mobile', $rules);
        $this->assertArrayNotHasKey('date_of_birth', $rules);
    }

    public function test_customer_edits_personal_details(): void
    {
        $rules = $this->profiles->rules(new User(['role' => User::ROLE_CUSTOMER]));

        $this->assertArrayHasKey('date_of_birth', $rules);
        $this->assertArrayHasKey('preferred_language', $rules);
        $this->assertArrayNotHasKey('firm_name', $rules);
        $this->assertArrayNotHasKey('mobile', $rules);
    }

    public function test_profile_photo_url_is_built_from_the_stored_path(): void
    {
        $user = new User;
        $this->assertNull($user->profile_photo_url);

        $user->profile_photo = 'uploads/profile-photos/me.jpg';
        $this->assertSame(asset('uploads/profile-photos/me.jpg'), $user->profile_photo_url);
    }

    public function test_profile_routes_are_registered_for_both_apps(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => implode('|', $route->methods()).' '.$route->uri())
            ->all();

        foreach (['customer', 'dealer'] as $prefix) {
            $this->assertContains("POST api/v1/{$prefix}/profile", $routes);
            $this->assertContains("POST api/v1/{$prefix}/profile/photo", $routes);
        }
    }
}
