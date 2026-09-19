<?php

namespace App\Services\Notifications;

use App\Contracts\Notifications\NotificationTemplateContract;
use App\Data\Notifications\NotificationMessage;

final class NotificationTemplateService implements NotificationTemplateContract
{
    public function make(string $event, string $status, array $replace = [], array $data = []): ?NotificationMessage
    {
        $template = config("notifications.templates.{$event}.{$status}");
        if (! is_array($template) || count($template) < 2) {
            return null;
        }

        $title = $this->fill((string) $template[0], $replace);
        $message = $this->fill((string) $template[1], $replace);
        if (trim($title) === '' || trim($message) === '') {
            return null;
        }

        return new NotificationMessage(
            title: $title,
            message: $message,
            type: $event,
            data: array_map(static fn ($value): string => (string) $value, array_filter(
                $data + ['status' => $status],
                static fn ($value): bool => $value !== null,
            )),
        );
    }

    /**
     * @param  array<string, scalar|null>  $replace
     */
    private function fill(string $text, array $replace): string
    {
        // Longest keys first so `:amount_due` is not cut by `:amount`.
        uksort($replace, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($replace as $key => $value) {
            $text = str_replace(':'.$key, (string) $value, $text);
        }

        return $text;
    }
}
