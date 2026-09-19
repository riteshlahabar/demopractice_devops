<?php

namespace App\Services\Auth;

use App\Contracts\Auth\PasswordChangeContract;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class PasswordChangeService implements PasswordChangeContract
{
    /** Domain of the placeholder emails given to accounts created by mobile OTP. */
    private const VIRTUAL_EMAIL_SUFFIX = '.bawaskar.local';

    public function hasPassword(User $user): bool
    {
        return $user->password_set_at !== null
            || ! str_ends_with(strtolower((string) $user->email), self::VIRTUAL_EMAIL_SUFFIX);
    }

    public function currentPasswordMatches(User $user, ?string $currentPassword): bool
    {
        if (! $this->hasPassword($user)) {
            return true;
        }

        return is_string($currentPassword)
            && $currentPassword !== ''
            && Hash::check($currentPassword, (string) $user->getAuthPassword());
    }

    public function change(User $user, string $newPassword, ?string $keepToken = null): void
    {
        $user->forceFill([
            'password' => $newPassword,
            'password_set_at' => now(),
        ])->save();

        $user->apiTokens()
            ->when(
                $keepToken !== null && $keepToken !== '',
                fn ($query) => $query->where('token_hash', '!=', hash('sha256', (string) $keepToken)),
            )
            ->delete();
    }
}
