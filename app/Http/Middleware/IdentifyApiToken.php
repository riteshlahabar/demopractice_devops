<?php

namespace App\Http\Middleware;

use App\Contracts\Auth\ApiTokenGuardContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifies the caller on public API routes without requiring a login.
 *
 * The catalog is public, but an approved dealer's token unlocks dealer
 * pricing. Without this, `$request->user()` is always null on those routes, so
 * a logged-in dealer was refused with 401. A missing, unknown or expired token
 * is simply treated as a guest — this middleware never blocks a request.
 */
class IdentifyApiToken
{
    public function __construct(private readonly ApiTokenGuardContract $guard) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->guard->resolve($request);

        if ($user) {
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}
