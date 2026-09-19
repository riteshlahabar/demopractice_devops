<?php

namespace App\Observers\Notifications;

use App\Models\Hr\Announcement;
use Illuminate\Support\Str;

/**
 * Broadcasts an announcement to its audience the moment it is published.
 * One scheduled for a future date is not pushed later; it still appears in
 * the app's announcement list on that date.
 */
final class AnnouncementNotificationObserver extends NotificationObserver
{
    public function saved(Announcement $announcement): void
    {
        $publishedNow = $announcement->wasRecentlyCreated
            ? $announcement->published_at !== null
            : $announcement->wasChanged('published_at') && ($announcement->getPrevious()['published_at'] ?? null) === null;

        if (! $publishedNow || $announcement->published_at === null || $announcement->published_at->isFuture()) {
            return;
        }

        $this->attempt(function () use ($announcement): void {
            $message = $this->templates->make('announcement', 'published', [
                'title' => $announcement->title,
                'body' => Str::limit(strip_tags((string) $announcement->body), 180),
            ], ['announcement_id' => $announcement->getKey()]);

            if ($message) {
                $this->sender->toAudience((string) ($announcement->audience ?: 'salesman'), $message);
            }
        });
    }
}
