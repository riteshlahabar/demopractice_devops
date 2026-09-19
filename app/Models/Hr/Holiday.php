<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['title', 'holiday_date', 'holiday_type', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['holiday_date' => 'date', 'is_active' => 'boolean'];
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('is_active', true)->whereYear('holiday_date', $year);
    }
}
