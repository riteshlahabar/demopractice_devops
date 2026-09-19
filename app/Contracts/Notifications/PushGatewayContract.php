<?php

namespace App\Contracts\Notifications;

use App\Data\Notifications\NotificationMessage;

/**
 * The push provider (Firebase Cloud Messaging, or a log-only stand-in).
 */
interface PushGatewayContract
{
    /**
     * @param  list<string>  $tokens
     * @return list<string> tokens the provider reported as no longer valid
     */
    public function toTokens(array $tokens, NotificationMessage $message): array;

    public function toTopic(string $topic, NotificationMessage $message): void;
}
