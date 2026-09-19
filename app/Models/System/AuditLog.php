<?php

namespace App\Models\System;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One recorded change. Rows are written by AuditRecorder and are never edited
 * from the admin panel — the module is read-only by design, because an audit
 * trail anyone can rewrite is not an audit trail.
 */
class AuditLog extends Model
{
    public const EVENTS = ['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'];

    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'event', 'auditable_type', 'auditable_id',
        'label', 'old_values', 'new_values', 'ip_address', 'url', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * "Product" rather than "App\Models\Catalog\Product" for the listing.
     */
    public function getRecordTypeAttribute(): string
    {
        return class_basename((string) $this->auditable_type);
    }

    public function getEventLabelAttribute(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }

    /**
     * Short "field: old → new" summary of what actually changed.
     */
    public function getChangeSummaryAttribute(): string
    {
        $new = $this->new_values ?? [];
        $old = $this->old_values ?? [];

        if ($new === [] && $old === []) {
            return '—';
        }

        $parts = [];
        foreach (array_slice(array_keys($new ?: $old), 0, 4) as $field) {
            $parts[] = $this->event === 'updated'
                ? $field.': '.$this->short($old[$field] ?? null).' → '.$this->short($new[$field] ?? null)
                : $field.': '.$this->short($new[$field] ?? ($old[$field] ?? null));
        }

        $remaining = count($new ?: $old) - count($parts);

        return implode(', ', $parts).($remaining > 0 ? " (+{$remaining} more)" : '');
    }

    private function short(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        $text = is_scalar($value) ? (string) $value : json_encode($value);

        return mb_strlen((string) $text) > 40 ? mb_substr((string) $text, 0, 40).'…' : (string) $text;
    }
}
