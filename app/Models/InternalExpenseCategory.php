<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalExpenseCategory extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(InternalExpenseSubcategory::class, 'category_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(InternalExpense::class, 'category_id');
    }
}
