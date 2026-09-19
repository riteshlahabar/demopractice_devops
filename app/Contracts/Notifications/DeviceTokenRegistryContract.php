<?php

namespace App\Contracts\Notifications;

interface DeviceTokenRegistryContract
{
    public function register(int $userId, string $token, string $app, ?string $platform): void;

    public function forget(int $userId, string $token): void;

    /**
     * @return list<string>
     */
    public function tokensFor(int $userId): array;

    /**
     * @param  list<string>  $tokens
     */
    public function discard(array $tokens): void;
}
