<?php

namespace App\Http\Controllers\Api\Shared;

use App\Contracts\Notifications\DeviceTokenRegistryContract;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Notifications\DeviceTokenRequest;
use Illuminate\Http\JsonResponse;

/**
 * The phone's push token, saved after login and removed on logout. The app
 * is taken from the account's role, never from the request.
 */
final class DeviceTokenController extends ApiController
{
    public function __construct(private readonly DeviceTokenRegistryContract $tokens) {}

    public function store(DeviceTokenRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $this->tokens->register((int) $user->id, (string) $request->validated('token'), (string) $user->role, $request->validated('platform'));

        return $this->success(['topic' => config('notifications.topics.'.$user->role)], 'Device registered.');
    }

    public function destroy(DeviceTokenRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $this->tokens->forget((int) $user->id, (string) $request->validated('token'));

        return $this->success([], 'Device removed.');
    }
}
