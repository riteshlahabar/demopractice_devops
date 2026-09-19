<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    public const MODES = ['classroom' => 'Classroom', 'online' => 'Online', 'on_the_job' => 'On The Job'];

    public const STATUSES = [
        'planned' => 'Planned', 'ongoing' => 'Ongoing',
        'completed' => 'Completed', 'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'title', 'trainer', 'mode', 'venue', 'starts_on', 'ends_on',
        'duration_hours', 'description', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date', 'ends_on' => 'date',
            'duration_hours' => 'integer', 'is_active' => 'boolean',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TrainingAttendance::class);
    }
}
