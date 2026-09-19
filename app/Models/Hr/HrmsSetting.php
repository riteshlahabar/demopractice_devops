<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class HrmsSetting extends Model
{
    public const WORKING_DAYS_BASIS = [
        'calendar' => 'Calendar days in the month',
        'fixed' => 'Fixed days per month',
    ];

    public const ROUNDING_MODES = [
        'nearest' => 'Nearest rupee',
        'up' => 'Round up',
        'down' => 'Round down',
        'none' => 'No rounding (paise kept)',
    ];

    protected $fillable = [
        'grace_minutes', 'half_day_minutes', 'full_day_minutes', 'late_marks_per_absent',
        'auto_mark_absent', 'default_weekly_offs', 'working_days_basis', 'fixed_working_days',
        'payroll_cycle_day', 'deduct_unpaid_leave', 'deduct_absent_days', 'rounding_mode',
    ];

    /**
     * Mirrors the column defaults in the migration, so an unsaved instance —
     * what HrmsSettingsService returns when the row is missing or the database
     * is unreachable — still answers every rule with a sane value.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'grace_minutes' => 10,
        'half_day_minutes' => 240,
        'full_day_minutes' => 480,
        'late_marks_per_absent' => 3,
        'auto_mark_absent' => false,
        'default_weekly_offs' => '["sunday"]',
        'working_days_basis' => 'calendar',
        'fixed_working_days' => 26,
        'payroll_cycle_day' => 1,
        'deduct_unpaid_leave' => true,
        'deduct_absent_days' => true,
        'rounding_mode' => 'nearest',
    ];

    protected function casts(): array
    {
        return [
            'grace_minutes' => 'integer',
            'half_day_minutes' => 'integer',
            'full_day_minutes' => 'integer',
            'late_marks_per_absent' => 'integer',
            'fixed_working_days' => 'integer',
            'payroll_cycle_day' => 'integer',
            'default_weekly_offs' => 'array',
            'auto_mark_absent' => 'boolean',
            'deduct_unpaid_leave' => 'boolean',
            'deduct_absent_days' => 'boolean',
        ];
    }

    /**
     * Rupee value rounded the way HR configured it.
     */
    public function round(float $amount): float
    {
        return match ($this->rounding_mode) {
            'up' => (float) ceil($amount),
            'down' => (float) floor($amount),
            'none' => round($amount, 2),
            default => (float) round($amount),
        };
    }
}
