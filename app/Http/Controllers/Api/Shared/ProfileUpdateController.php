<?php

namespace App\Http\Controllers\Api\Shared;

use App\Contracts\Account\ProfileUpdateContract;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Edit profile and upload profile photo from the apps' Account screen.
 */
final class ProfileUpdateController extends ApiController
{
    public function __construct(private readonly ProfileUpdateContract $profiles) {}

    public function update(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate($this->profiles->rules($user));

        return $this->success(['user' => $this->profiles->update($user, $validated)], 'Profile updated.');
    }

    public function photo(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $this->profiles->updatePhoto($user, $request->file('photo'));

        return $this->success(['profile_photo_url' => $user->profile_photo_url], 'Profile photo updated.');
    }
}
