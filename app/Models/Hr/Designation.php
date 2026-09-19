<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends Model
{
    protected $fillable = ['department_id', 'name', 'code', 'level', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['level' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
