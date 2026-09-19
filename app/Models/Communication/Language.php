<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'is_default', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_default')->orderBy('sort_order')->orderBy('name');
    }
}
