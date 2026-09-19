<?php

namespace App\Http\Controllers\Api\Shared;

use App\Contracts\Auth\PasswordChangeContract;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Change password from the customer, dealer and salesman apps.
 */
final class ChangePasswordController extends ApiController
{
    public function __construct(private readonly PasswordChangeContract $passwords) {}

    /**
     * Tells the app whether to ask for the current password.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['has_password' => $this->passwords->hasPassword($user)]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'current_password' => ['nullable', 'string', 'max:64'],
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
        ]);

        if (! $this->passwords->currentPasswordMatches($user, $validated['current_password'] ?? null)) {
            return $this->fail('Current password is incorrect.', 422, [
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        if ($this->passwords->hasPassword($user) && Hash::check($validated['password'], (string) $user->getAuthPassword())) {
            return $this->fail('New password must be different from the current one.', 422, [
                'password' => ['New password must be different from the current one.'],
            ]);
        }

        $this->passwords->change($user, $validated['password'], $request->bearerToken());

        return $this->success([], 'Password changed. Other devices have been signed out.');
    }
}
