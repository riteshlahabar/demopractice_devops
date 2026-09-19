<?php

namespace App\Http\Middleware;

use App\Contracts\Auth\ApiTokenGuardContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces bearer token authentication (and optionally a role) for API routes.
 *
 * Applied on the route group so a new endpoint is protected by default. The
 * per-controller `requireUser()` calls stay in place as a second layer; both
 * now read the same guard, so they cannot disagree.
 */
class AuthenticateApiToken
{
    public function __construct(private readonly ApiTokenGuardContract $guard) {}

    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $this->guard->resolve($request, $role);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated or unauthorized.',
                'errors' => [],
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
