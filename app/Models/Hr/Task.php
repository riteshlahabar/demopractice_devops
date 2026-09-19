<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public const PRIORITIES = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'title', 'description', 'assigned_to', 'assigned_by', 'dealer_id',
        'priority', 'status', 'due_date', 'completed_at', 'completion_notes',
    ];

    protected $appends = ['is_overdue'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    /**
     * Past its due date and still open. Completed and cancelled tasks are
     * never overdue, however old.
     */
    public function isOverdue(): bool
    {
        if ($this->due_date === null || in_array($this->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        return $this->due_date->endOfDay()->isPast();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }
}
