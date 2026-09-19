<?php

namespace App\Contracts\Notifications;

use App\Data\Notifications\NotificationMessage;

/**
 * Builds the automatic notification for an event + status from
 * config/notifications.php, or null when that status sends nothing.
 */
interface NotificationTemplateContract
{
    /**
     * @param  array<string, scalar|null>  $replace  `:key` placeholders
     * @param  array<string, scalar|null>  $data  tap target sent to the app
     */
    public function make(string $event, string $status, array $replace = [], array $data = []): ?NotificationMessage;
}
