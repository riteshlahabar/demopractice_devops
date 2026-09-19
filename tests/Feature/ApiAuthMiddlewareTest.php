<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateApiToken;
use App\Models\User;
use App\Services\Auth\ApiTokenGuard;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiAuthMiddlewareTest extends TestCase
{
    private function requestFor(?User $user): Request
    {
        $request = Request::create('/api/dealer/orders', 'GET');

        if ($user) {
            $request->attributes->set('bawaskar_api_user', $user);
        }

        return $request;
    }

    private function user(string $role, string $status = 'active'): User
    {
        $user = new User(['name' => 'Test']);
        $user->forceFill(['id' => 1, 'role' => $role, 'status' => $status]);

        return $user;
    }

    public function test_a_customer_token_is_refused_on_a_dealer_route(): void
    {
        $response = (new AuthenticateApiToken(new ApiTokenGuard))->handle(
            $this->requestFor($this->user(User::ROLE_CUSTOMER)),
            fn () => response('should not be reached'),
            User::ROLE_DEALER,
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertFalse(json_decode($response->getContent(), true)['success']);
    }

    public function test_a_request_without_a_token_is_refused(): void
    {
        $response = (new AuthenticateApiToken(new ApiTokenGuard))->handle(
            $this->requestFor(null),
            fn () => response('should not be reached'),
            User::ROLE_DEALER,
        );

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_the_matching_role_passes_through_and_the_request_carries_the_user(): void
    {
        $dealer = $this->user(User::ROLE_DEALER);
        $seen = null;

        (new AuthenticateApiToken(new ApiTokenGuard))->handle(
            $this->requestFor($dealer),
            function (Request $request) use (&$seen) {
                $seen = $request->user();

                return response('ok');
            },
            User::ROLE_DEALER,
        );

        $this->assertSame($dealer, $seen, 'Controllers read the caller from the request, so it has to be set.');
    }
}
