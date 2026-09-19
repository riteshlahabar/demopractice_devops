<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = ['salesman_id', 'attendance_date', 'check_in_at', 'check_out_at', 'check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude', 'working_minutes', 'status'];

    protected function casts(): array
    {
        return ['attendance_date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
