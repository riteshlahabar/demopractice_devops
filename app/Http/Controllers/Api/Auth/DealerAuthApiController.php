<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;

abstract class DealerAuthApiController extends AuthApiController
{
    /**
     * A dealer only gets a token once an admin has approved the account, so a
     * successful phone verification is not by itself a successful login.
     */
    protected function respondToDealer(User $user, bool $approved): JsonResponse
    {
        if (! $approved) {
            return $this->success(['user' => $user], 'Dealer registered. Admin approval required.', 201);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success([
            'user' => $user,
            'token' => $user->createApiToken('dealer-app'),
        ], 'Dealer logged in.');
    }
}
