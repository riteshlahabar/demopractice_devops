<?php

namespace App\Providers;

use App\Contracts\System\AuditRecorderContract;
use App\Services\System\AuditRecorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the audit trail to Eloquent's own model events rather than to a trait,
 * so a model does not have to opt in and a newly added model is audited
 * automatically. What is *not* audited is listed in config/audit.php.
 */
class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // A singleton, because withoutRecording() toggles state that must be
        // visible to every caller for the length of the callback.
        $this->app->singleton(AuditRecorderContract::class, AuditRecorder::class);
    }

    public function boot(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $eventName, array $payload) use ($event): void {
                $model = $payload[0] ?? null;

                if ($model instanceof Model) {
                    $this->app->make(AuditRecorderContract::class)->record($event, $model);
                }
            });
        }
    }
}
