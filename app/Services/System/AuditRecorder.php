<?php

namespace App\Services\System;

use App\Contracts\System\AuditRecorderContract;
use App\Models\System\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

/**
 * Writes the audit trail. Only the columns that actually changed are stored,
 * never a whole copy of the record, and redacted columns (passwords, tokens)
 * record the fact of the change without the value. Every failure is swallowed:
 * an audit row must never be the reason a save fails.
 */
class AuditRecorder implements AuditRecorderContract
{
    private bool $enabled = true;

    public function record(string $event, Model $model): void
    {
        if (! $this->enabled || ! config('audit.enabled', true) || $this->isIgnored($model)) {
            return;
        }

        try {
            [$old, $new] = $this->values($event, $model);

            if ($event === 'updated' && $new === []) {
                return;
            }

            AuditLog::query()->create($this->row($event, $model, $old, $new));
        } catch (Throwable) {
            // Auditing is observational; it never blocks the caller.
        }
    }

    public function withoutRecording(callable $callback): mixed
    {
        $previous = $this->enabled;
        $this->enabled = false;

        try {
            return $callback();
        } finally {
            $this->enabled = $previous;
        }
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function values(string $event, Model $model): array
    {
        $ignored = (array) config('audit.ignore_columns', []);

        if ($event === 'created') {
            return [[], $this->clean(array_diff_key($model->getAttributes(), array_flip($ignored)))];
        }

        if ($event === 'deleted') {
            return [$this->clean(array_diff_key($model->getOriginal(), array_flip($ignored))), []];
        }

        $changed = array_diff_key($model->getChanges(), array_flip($ignored));
        $original = array_intersect_key($model->getOriginal(), $changed);

        return [$this->clean($original), $this->clean($changed)];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function clean(array $values): array
    {
        $redact = (array) config('audit.redact', []);

        foreach ($values as $column => $value) {
            if (in_array($column, $redact, true)) {
                $values[$column] = '[redacted]';

                continue;
            }

            if (is_string($value) && mb_strlen($value) > 500) {
                $values[$column] = mb_substr($value, 0, 500).'…';
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @return array<string, mixed>
     */
    private function row(string $event, Model $model, array $old, array $new): array
    {
        $user = Auth::user();

        return [
            'user_id' => $user?->getAuthIdentifier(),
            'user_name' => $user instanceof User ? $user->name : null,
            'user_role' => $user instanceof User ? $user->role : null,
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => (int) $model->getKey(),
            'label' => $this->label($model),
            'old_values' => $old === [] ? null : $old,
            'new_values' => $new === [] ? null : $new,
            'ip_address' => Request::ip(),
            'url' => mb_substr((string) Request::fullUrl(), 0, 2048),
            'user_agent' => mb_substr((string) Request::userAgent(), 0, 512),
        ];
    }

    private function label(Model $model): ?string
    {
        foreach ((array) config('audit.label_attributes', []) as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return mb_substr($value, 0, 255);
            }
        }

        return null;
    }

    private function isIgnored(Model $model): bool
    {
        foreach ((array) config('audit.ignore', []) as $ignored) {
            if ($model instanceof $ignored) {
                return true;
            }
        }

        return false;
    }
}
