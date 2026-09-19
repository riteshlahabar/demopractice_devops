<?php

namespace App\Services\Auth;

use App\Contracts\Auth\ApiTokenGuardContract;
use App\Models\Auth\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;

final class ApiTokenGuard implements ApiTokenGuardContract
{
    /**
     * Request attribute holding the user already resolved by the middleware,
     * so a controller asking again does not repeat the token lookup.
     */
    private const RESOLVED_ATTRIBUTE = 'bawaskar_api_user';

    public function resolve(Request $request, ?string $role = null): ?User
    {
        $user = $request->attributes->get(self::RESOLVED_ATTRIBUTE) ?: $this->fromBearerToken($request);

        if (! $user instanceof User || $user->status !== 'active') {
            return null;
        }

        if ($role !== null && $user->role !== $role) {
            return null;
        }

        $request->attributes->set(self::RESOLVED_ATTRIBUTE, $user);

        return $user;
    }

    private function fromBearerToken(Request $request): ?User
    {
        $bearer = $request->bearerToken();

        if (blank($bearer)) {
            return null;
        }

        $apiToken = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->first();

        // A token without an expiry is treated as invalid: every token issued
        // by User::createApiToken() carries one, so a null here means a stale
        // row that predates token expiry.
        if (! $apiToken || ! $apiToken->expires_at || $apiToken->expires_at->isPast()) {
            return null;
        }

        if (! $apiToken->user) {
            return null;
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        return $apiToken->user;
    }
}
