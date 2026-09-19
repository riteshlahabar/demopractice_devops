<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'leave_type', 'label', 'annual_days', 'is_paid', 'carry_forward',
        'max_carry_forward_days', 'min_notice_days', 'max_consecutive_days',
        'requires_approval', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'annual_days' => 'decimal:2',
            'max_carry_forward_days' => 'decimal:2',
            'min_notice_days' => 'integer',
            'max_consecutive_days' => 'integer',
            'sort_order' => 'integer',
            'is_paid' => 'boolean',
            'carry_forward' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
