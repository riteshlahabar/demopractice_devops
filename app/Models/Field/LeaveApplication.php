<?php

namespace App\Models\Field;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApplication extends Model
{
    protected $fillable = ['salesman_id', 'leave_type', 'from_date', 'to_date', 'reason', 'status', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['from_date' => 'date', 'to_date' => 'date', 'approved_at' => 'datetime'];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
