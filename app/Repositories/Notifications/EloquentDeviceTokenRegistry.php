<?php

namespace App\Repositories\Notifications;

use App\Contracts\Notifications\DeviceTokenRegistryContract;
use App\Models\Communication\DeviceToken;

final class EloquentDeviceTokenRegistry implements DeviceTokenRegistryContract
{
    public function register(int $userId, string $token, string $app, ?string $platform): void
    {
        // Keyed on the token: a phone that changes account moves to the new user.
        DeviceToken::query()->updateOrCreate(
            ['token' => $token],
            ['user_id' => $userId, 'app' => $app, 'platform' => $platform, 'last_seen_at' => now()],
        );
    }

    public function forget(int $userId, string $token): void
    {
        DeviceToken::query()->where('user_id', $userId)->where('token', $token)->delete();
    }

    public function tokensFor(int $userId): array
    {
        return DeviceToken::query()
            ->where('user_id', $userId)
            ->pluck('token')
            ->map(static fn ($token): string => (string) $token)
            ->values()
            ->all();
    }

    public function discard(array $tokens): void
    {
        if ($tokens !== []) {
            DeviceToken::query()->whereIn('token', $tokens)->delete();
        }
    }
}
