<?php

namespace App\Data\Notifications;

/**
 * One notification as the apps see it: text plus a `type` and `data` that
 * tell the app which screen a tap should open (e.g. type `order`, data
 * `order_id`). Data values are strings because FCM only carries strings.
 */
final readonly class NotificationMessage
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'general',
        public array $data = [],
        public string $channel = 'push',
    ) {}

    /**
     * What is stored in `notifications.payload` and sent as the push data.
     *
     * @return array<string, string>
     */
    public function payload(): array
    {
        return ['type' => $this->type] + $this->data;
    }
}
