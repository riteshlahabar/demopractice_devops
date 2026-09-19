<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resignation extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn',
        'completed' => 'Completed',
    ];

    public const SETTLEMENT_STATUSES = ['pending' => 'Pending', 'settled' => 'Settled'];

    protected $fillable = [
        'salesman_id', 'reference_no', 'resignation_date', 'notice_period_days',
        'requested_last_working_date', 'approved_last_working_date', 'reason', 'status',
        'notice_period_waived', 'approved_by', 'approved_at', 'exit_interview_notes',
        'pending_salary', 'leave_encashment', 'other_dues', 'advance_recovery',
        'other_recovery', 'settlement_amount', 'settlement_status', 'settled_on',
        'assets_returned', 'documents_handed_over', 'settlement_notes',
    ];

    protected function casts(): array
    {
        return [
            'resignation_date' => 'date',
            'requested_last_working_date' => 'date',
            'approved_last_working_date' => 'date',
            'settled_on' => 'date',
            'approved_at' => 'datetime',
            'notice_period_days' => 'integer',
            'notice_period_waived' => 'boolean',
            'assets_returned' => 'boolean',
            'documents_handed_over' => 'boolean',
            'pending_salary' => 'decimal:2',
            'leave_encashment' => 'decimal:2',
            'other_dues' => 'decimal:2',
            'advance_recovery' => 'decimal:2',
            'other_recovery' => 'decimal:2',
            'settlement_amount' => 'decimal:2',
        ];
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Dues minus recoveries. Never negative on the payout side — a net
     * recovery is shown as zero payable and chased separately.
     */
    public function computedSettlement(): float
    {
        $dues = (float) $this->pending_salary + (float) $this->leave_encashment + (float) $this->other_dues;
        $recovery = (float) $this->advance_recovery + (float) $this->other_recovery;

        return round(max(0, $dues - $recovery), 2);
    }

    /**
     * The last working date the notice period implies, used when the admin
     * has not set one by hand.
     */
    public function noticePeriodEndsOn(): ?string
    {
        if ($this->resignation_date === null) {
            return null;
        }

        return $this->notice_period_waived
            ? $this->resignation_date->toDateString()
            : $this->resignation_date->copy()->addDays((int) $this->notice_period_days)->toDateString();
    }
}
