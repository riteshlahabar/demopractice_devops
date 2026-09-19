<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAttendance extends Model
{
    public const STATUSES = [
        'enrolled' => 'Enrolled', 'attended' => 'Attended',
        'absent' => 'Absent', 'completed' => 'Completed',
    ];

    protected $fillable = [
        'training_program_id', 'salesman_id', 'status', 'score',
        'certificate_issued', 'certificate_path', 'remarks',
    ];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'certificate_issued' => 'boolean'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
