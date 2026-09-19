<?php

namespace App\Models\System;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    public const STATUSES = ['running' => 'Running', 'completed' => 'Completed', 'failed' => 'Failed'];

    protected $fillable = [
        'filename', 'disk', 'path', 'type', 'status', 'size_bytes',
        'table_count', 'error', 'created_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer', 'table_count' => 'integer', 'completed_at' => 'datetime'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSizeLabelAttribute(): string
    {
        $bytes = (int) $this->size_bytes;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, $unit === 'B' ? 0 : 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
