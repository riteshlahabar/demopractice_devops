<?php

namespace App\Contracts\Notifications;

use App\Data\Notifications\NotificationMessage;

/**
 * Writes notifications to the app inbox and pushes them to the phones.
 * A push failure never throws: the inbox row is the source of truth.
 */
interface NotificationSenderContract
{
    public function toUser(int $userId, NotificationMessage $message): void;

    /**
     * @param  string  $audience  customer | dealer | salesman | all
     * @return int number of inbox rows written
     */
    public function toAudience(string $audience, NotificationMessage $message): int;
}
