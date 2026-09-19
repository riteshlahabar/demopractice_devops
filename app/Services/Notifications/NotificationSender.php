<?php

namespace App\Services\Notifications;

use App\Contracts\Notifications\DeviceTokenRegistryContract;
use App\Contracts\Notifications\NotificationSenderContract;
use App\Contracts\Notifications\PushGatewayContract;
use App\Data\Notifications\NotificationMessage;
use App\Models\Communication\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Inbox first, push second. The inbox row is what the app lists, so it is
 * written even when the push provider is down or not configured yet.
 *
 * A broadcast writes one inbox row per active account (so each person has
 * their own read state) but sends a single push to the role's FCM topic
 * instead of one request per phone.
 */
final class NotificationSender implements NotificationSenderContract
{
    private const AUDIENCE_ROLES = [
        'customer' => [User::ROLE_CUSTOMER],
        'dealer' => [User::ROLE_DEALER],
        'salesman' => [User::ROLE_SALESMAN],
        'all' => [User::ROLE_CUSTOMER, User::ROLE_DEALER, User::ROLE_SALESMAN],
    ];

    public function __construct(
        private readonly PushGatewayContract $push,
        private readonly DeviceTokenRegistryContract $tokens,
    ) {}

    public function toUser(int $userId, NotificationMessage $message): void
    {
        Notification::query()->create([
            'user_id' => $userId,
            'channel' => $message->channel,
            'title' => $message->title,
            'message' => $message->message,
            'payload' => $message->payload(),
        ]);

        $this->safely(function () use ($userId, $message): void {
            $tokens = $this->tokens->tokensFor($userId);
            if ($tokens !== []) {
                $this->tokens->discard($this->push->toTokens($tokens, $message));
            }
        });
    }

    public function toAudience(string $audience, NotificationMessage $message): int
    {
        $roles = self::AUDIENCE_ROLES[$audience] ?? [];
        if ($roles === []) {
            return 0;
        }

        $written = 0;
        $payload = json_encode($message->payload());
        $now = now();

        User::query()
            ->whereIn('role', $roles)
            ->where('status', 'active')
            ->select('id')
            ->chunkById(500, function ($users) use ($message, $payload, $now, &$written): void {
                $rows = $users->map(fn (User $user): array => [
                    'user_id' => $user->id,
                    'channel' => $message->channel,
                    'title' => $message->title,
                    'message' => $message->message,
                    'payload' => $payload,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                Notification::query()->insert($rows);
                $written += count($rows);
            });

        foreach ($roles as $role) {
            $topic = config('notifications.topics.'.$role);
            if (is_string($topic) && $topic !== '') {
                $this->safely(fn () => $this->push->toTopic($topic, $message));
            }
        }

        return $written;
    }

    private function safely(callable $send): void
    {
        try {
            $send();
        } catch (Throwable $failure) {
            Log::warning('Push notification failed: '.$failure->getMessage());
        }
    }
}
