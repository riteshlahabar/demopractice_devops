<?php

namespace Tests\Unit;

use App\Contracts\Auth\ApiTokenGuardContract;
use App\Http\Middleware\IdentifyApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IdentifyApiTokenTest extends TestCase
{
    public function test_known_token_sets_the_request_user(): void
    {
        $user = new User(['role' => User::ROLE_DEALER]);

        $this->assertSame($user, $this->userSeenBy(new IdentifyApiToken($this->guardReturning($user))));
    }

    public function test_guest_request_passes_through_without_a_user(): void
    {
        $this->assertNull($this->userSeenBy(new IdentifyApiToken($this->guardReturning(null))));
    }

    public function test_dealer_catalog_routes_identify_the_caller(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        foreach (['api/v1/catalog/homepage', 'api/v1/catalog/products', 'api/v1/catalog/categories'] as $uri) {
            $route = $routes->first(fn ($route) => $route->uri() === $uri);

            $this->assertNotNull($route, "Route {$uri} is missing.");
            $this->assertContains('api.identify', $route->gatherMiddleware(), "Route {$uri} does not identify the caller.");
        }
    }

    private function userSeenBy(IdentifyApiToken $middleware): ?User
    {
        $seen = false;

        $middleware->handle(Request::create('/api/v1/catalog/homepage'), function (Request $request) use (&$seen): Response {
            $seen = $request->user();

            return new Response('ok');
        });

        $this->assertNotFalse($seen, 'The middleware did not pass the request on.');

        return $seen;
    }

    private function guardReturning(?User $user): ApiTokenGuardContract
    {
        return new class($user) implements ApiTokenGuardContract
        {
            public function __construct(private readonly ?User $user) {}

            public function resolve(Request $request, ?string $role = null): ?User
            {
                return $this->user;
            }
        };
    }
}
