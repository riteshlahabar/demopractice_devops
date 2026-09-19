<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalExpenseSubcategory extends Model
{
    protected $fillable = ['category_id', 'name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InternalExpenseCategory::class, 'category_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(InternalExpense::class, 'subcategory_id');
    }
}
