<?php

namespace App\Services\Notifications\Push;

use App\Contracts\Notifications\PushGatewayContract;
use App\Data\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Log;

/**
 * Used until Firebase is set up (PUSH_DRIVER=log): records what would have
 * been pushed so the flow can be checked in laravel.log.
 */
final class LogPushGateway implements PushGatewayContract
{
    public function toTokens(array $tokens, NotificationMessage $message): array
    {
        Log::info('[push:log] to '.count($tokens).' device(s)', ['title' => $message->title, 'data' => $message->payload()]);

        return [];
    }

    public function toTopic(string $topic, NotificationMessage $message): void
    {
        Log::info('[push:log] to topic '.$topic, ['title' => $message->title, 'data' => $message->payload()]);
    }
}
