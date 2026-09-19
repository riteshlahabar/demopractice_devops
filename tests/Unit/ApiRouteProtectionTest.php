<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiRouteProtectionTest extends TestCase
{
    /**
     * Endpoints that are deliberately public: the storefront catalog the apps
     * read before anyone signs in, the auth handshake itself, and the location
     * dropdowns a registration form needs before an account exists.
     */
    private const PUBLIC_PREFIXES = ['health', 'auth/', 'catalog/', 'translations', 'app-translations', 'locations/'];

    public function test_every_non_public_api_route_is_behind_the_token_middleware(): void
    {
        $unprotected = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            $path = preg_replace('#^api/(v1/)?#', '', $uri);

            foreach (self::PUBLIC_PREFIXES as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    continue 2;
                }
            }

            $guarded = collect($route->gatherMiddleware())
                ->contains(fn (mixed $middleware): bool => is_string($middleware) && str_starts_with($middleware, 'api.auth'));

            if (! $guarded) {
                $unprotected[] = implode('|', $route->methods()).' '.$uri;
            }
        }

        $this->assertSame([], $unprotected, "These API routes have no api.auth middleware:\n".implode("\n", $unprotected));
    }

    /**
     * A route referencing a class that is not imported still registers fine and
     * only blows up when it is hit, so every route's controller and action is
     * checked here instead.
     */
    public function test_every_route_points_at_a_controller_action_that_exists(): void
    {
        $broken = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if ($action === 'Closure' || ! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action, 2);

            if (! class_exists($class) || ! method_exists($class, $method)) {
                $broken[] = $route->uri().' -> '.$action;
            }
        }

        $this->assertSame([], $broken, "These routes point at missing code:\n".implode("\n", $broken));
    }

    /**
     * Every module controller is built by the container, so a constructor or
     * binding mistake would only surface when someone opens that page. Around
     * forty admin controllers share one base class, which makes this the check
     * that a change to that base did not break any of them.
     */
    public function test_every_route_controller_can_be_built_by_the_container(): void
    {
        $failures = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if ($action === 'Closure' || ! str_contains($action, '@')) {
                continue;
            }

            $class = explode('@', $action, 2)[0];

            if (isset($failures[$class]) || ! class_exists($class)) {
                continue;
            }

            try {
                app($class);
            } catch (\Throwable $e) {
                $failures[$class] = $class.': '.$e->getMessage();
            }
        }

        $this->assertSame([], array_values($failures), "These controllers cannot be resolved:\n".implode("\n", $failures));
    }

    /**
     * Putting the token middleware on the login endpoints would lock everyone
     * out: you cannot present a token before you have one.
     */
    public function test_login_endpoints_are_not_behind_the_token_middleware(): void
    {
        $locked = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_contains($route->uri(), 'auth/')) {
                continue;
            }

            $guarded = collect($route->gatherMiddleware())
                ->contains(fn (mixed $middleware): bool => is_string($middleware) && str_starts_with($middleware, 'api.auth'));

            if ($guarded) {
                $locked[] = $route->uri();
            }
        }

        $this->assertSame([], $locked, "These auth routes are unreachable:\n".implode("\n", $locked));
    }

    public function test_admin_and_storefront_logins_are_rate_limited(): void
    {
        foreach (['admin.login.store', 'store.auth.login', 'store.auth.register'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route {$name} is missing.");
            $this->assertContains(
                'throttle:login',
                $route->gatherMiddleware(),
                "Route {$name} can be brute forced without a throttle."
            );
        }
    }
}
