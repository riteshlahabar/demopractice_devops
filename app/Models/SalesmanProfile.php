<?php

namespace App\Models;

use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesmanProfile extends Model
{
    public const EMPLOYMENT_STATUSES = [
        'active' => 'Active',
        'probation' => 'Probation',
        'notice_period' => 'Notice Period',
        'resigned' => 'Resigned',
        'exited' => 'Exited',
    ];

    protected $fillable = [
        'user_id', 'employee_code', 'department_id', 'designation_id', 'reporting_to',
        'joining_date', 'employment_status', 'confirmation_date', 'exit_date',
        'basic_salary', 'target_amount', 'territory',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'confirmation_date' => 'date',
            'exit_date' => 'date',
            'basic_salary' => 'decimal:2',
            'target_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }
}
