<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'name', 'starts_at', 'ends_at', 'grace_minutes', 'half_day_minutes',
        'weekly_offs', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weekly_offs' => 'array',
            'grace_minutes' => 'integer',
            'half_day_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
