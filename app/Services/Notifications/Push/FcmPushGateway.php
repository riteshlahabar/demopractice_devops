<?php

namespace App\Services\Notifications\Push;

use App\Contracts\Notifications\PushGatewayContract;
use App\Data\Notifications\NotificationMessage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Firebase Cloud Messaging, HTTP v1 API (PUSH_DRIVER=fcm).
 */
final class FcmPushGateway implements PushGatewayContract
{
    /** FCM error codes that mean the token will never work again. */
    private const DEAD_TOKEN_ERRORS = ['UNREGISTERED', 'INVALID_ARGUMENT', 'SENDER_ID_MISMATCH'];

    public function __construct(private readonly FcmAccessTokenProvider $auth) {}

    public function toTokens(array $tokens, NotificationMessage $message): array
    {
        $dead = [];

        foreach (array_unique($tokens) as $token) {
            $response = $this->send(['token' => $token], $message);
            if ($response->successful()) {
                continue;
            }

            $code = (string) collect((array) $response->json('error.details', []))->pluck('errorCode')->filter()->first();
            if (in_array($code, self::DEAD_TOKEN_ERRORS, true) || $response->status() === 404) {
                $dead[] = $token;
            } else {
                Log::warning('FCM send failed', ['status' => $response->status(), 'error' => $response->json('error.message')]);
            }
        }

        return $dead;
    }

    public function toTopic(string $topic, NotificationMessage $message): void
    {
        $response = $this->send(['topic' => $topic], $message);

        if (! $response->successful()) {
            Log::warning('FCM topic send failed', ['topic' => $topic, 'status' => $response->status(), 'error' => $response->json('error.message')]);
        }
    }

    /**
     * @param  array{token?: string, topic?: string}  $target
     * @return array<string, mixed>
     */
    public function body(array $target, NotificationMessage $message): array
    {
        return ['message' => $target + [
            'notification' => ['title' => $message->title, 'body' => $message->message],
            'data' => $message->payload(),
            'android' => ['priority' => 'high', 'notification' => ['channel_id' => 'bawaskar_default', 'sound' => 'default']],
            'apns' => ['payload' => ['aps' => ['sound' => 'default']]],
        ]];
    }

    /**
     * @param  array{token?: string, topic?: string}  $target
     */
    private function send(array $target, NotificationMessage $message): Response
    {
        $projectId = (string) config('notifications.push.fcm.project_id');
        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID is not set.');
        }

        return Http::withToken($this->auth->token())
            ->timeout((int) config('notifications.push.fcm.timeout', 10))
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $this->body($target, $message));
    }
}
