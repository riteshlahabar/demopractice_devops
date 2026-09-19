<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    public const REQUEST_TYPES = [
        'leave' => 'Leave Application',
        'expense' => 'Expense Claim',
        'advance' => 'Advance & Loan',
        'tour_plan' => 'Tour Plan',
        'salary' => 'Salary Slip',
    ];

    public const APPROVER_ROLES = [
        'admin' => 'Admin',
        'reporting_manager' => 'Reporting Manager',
    ];

    protected $fillable = [
        'request_type', 'level', 'approver_role', 'amount_from', 'amount_to', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'amount_from' => 'decimal:2',
            'amount_to' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getRequestTypeLabelAttribute(): string
    {
        return self::REQUEST_TYPES[$this->request_type] ?? $this->request_type;
    }
}
