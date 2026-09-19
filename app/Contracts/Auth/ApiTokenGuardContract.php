<?php

namespace App\Contracts\Auth;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * SRP: turns the request's bearer token into the user it belongs to.
 *
 * Kept behind a contract so the middleware and the API controllers share one
 * definition of "who is calling", instead of each endpoint re-implementing the
 * check. An endpoint that forgets the check is the failure mode this prevents.
 */
interface ApiTokenGuardContract
{
    /**
     * Returns the authenticated user, or null when the token is missing,
     * unknown, expired, belongs to an inactive user, or has the wrong role.
     */
    public function resolve(Request $request, ?string $role = null): ?User;
}
