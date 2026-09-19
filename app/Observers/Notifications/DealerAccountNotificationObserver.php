<?php

namespace App\Observers\Notifications;

use App\Models\User;

/**
 * Tells a dealer when admin activates their account.
 */
final class DealerAccountNotificationObserver extends NotificationObserver
{
    public function updated(User $user): void
    {
        if ($user->role !== User::ROLE_DEALER || ! $this->statusChanged($user) || $user->status !== 'active') {
            return;
        }

        $this->notify((int) $user->getKey(), 'dealer_account', 'active');
    }
}
