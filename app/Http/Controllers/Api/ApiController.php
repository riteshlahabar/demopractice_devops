<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function success(array $data = [], string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function fail(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    /**
     * The api.auth middleware has already resolved the bearer token and put the
     * user on the request, so this only re-checks the role. Reading it back
     * rather than resolving again keeps the controller free of any dependency
     * and fails closed: a route without the middleware has no user here.
     */
    protected function user(Request $request, ?string $role = null): ?User
    {
        $user = $request->user();

        if (! $user instanceof User || $user->status !== 'active') {
            return null;
        }

        return ($role === null || $user->role === $role) ? $user : null;
    }

    protected function requireUser(Request $request, ?string $role = null): User|JsonResponse
    {
        $user = $this->user($request, $role);

        return $user ?: $this->fail('Unauthenticated or unauthorized.', 401);
    }
}
