<?php

namespace App\Observers\Notifications;

use App\Contracts\Notifications\NotificationSenderContract;
use App\Contracts\Notifications\NotificationTemplateContract;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base for the observers that send automatic notifications.
 *
 * Runs after the transaction commits, so a rolled-back save never notifies,
 * and swallows every failure, so a notification problem can never stop an
 * order or approval from being saved.
 */
abstract class NotificationObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        protected readonly NotificationSenderContract $sender,
        protected readonly NotificationTemplateContract $templates,
    ) {}

    /**
     * @param  array<string, scalar|null>  $replace
     * @param  array<string, scalar|null>  $data
     */
    protected function notify(?int $userId, string $event, string $status, array $replace = [], array $data = []): void
    {
        if (! $userId) {
            return;
        }

        $this->attempt(function () use ($userId, $event, $status, $replace, $data): void {
            $message = $this->templates->make($event, $status, $replace, $data);
            if ($message) {
                $this->sender->toUser($userId, $message);
            }
        });
    }

    protected function statusChanged(Model $model, string $column = 'status'): bool
    {
        return $model->wasChanged($column) && (string) $model->getAttribute($column) !== '';
    }

    protected function attempt(callable $work): void
    {
        try {
            $work();
        } catch (Throwable $failure) {
            Log::warning('Automatic notification skipped: '.$failure->getMessage());
        }
    }

    protected function money(mixed $amount): string
    {
        return number_format((float) $amount, 2);
    }

    protected function date(mixed $value): string
    {
        return $value ? Carbon::parse($value)->format('d M Y') : '';
    }

    protected function label(mixed $value): string
    {
        return ucwords(str_replace('_', ' ', (string) $value));
    }
}
