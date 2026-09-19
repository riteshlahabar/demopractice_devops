<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSkill extends Model
{
    public const LEVELS = [
        'beginner' => 'Beginner', 'intermediate' => 'Intermediate',
        'advanced' => 'Advanced', 'expert' => 'Expert',
    ];

    protected $fillable = ['salesman_id', 'skill', 'level', 'certified_on', 'certified_by', 'remarks'];

    protected function casts(): array
    {
        return ['certified_on' => 'date'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
