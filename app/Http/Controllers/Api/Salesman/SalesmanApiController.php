<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Shared base for the salesman endpoints.
 *
 * Every salesman route sits behind `api.auth:salesman`, so the caller is
 * already resolved and role checked by the time an action runs; this just
 * hands it over without each method repeating the guard.
 */
abstract class SalesmanApiController extends ApiController
{
    protected function salesman(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->role === User::ROLE_SALESMAN, 401);

        return $user;
    }
}
