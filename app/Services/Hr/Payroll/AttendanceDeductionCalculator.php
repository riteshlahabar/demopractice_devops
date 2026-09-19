<?php

namespace App\Services\Hr\Payroll;

use App\Contracts\Hr\LeavePolicyContract;
use App\Models\Field\AttendanceLog;
use App\Models\Field\LeaveApplication;
use App\Models\Hr\HrmsSetting;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

/**
 * Loss of pay for the month: days marked absent, half days counted as half,
 * and approved leave on an unpaid leave type. Both rules can be switched off in
 * HRMS Settings, in which case the salesman is paid for every working day.
 */
class AttendanceDeductionCalculator
{
    public function __construct(private readonly LeavePolicyContract $leavePolicies) {}

    /**
     * Days that must not be paid for.
     */
    public function unpaidDays(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd, HrmsSetting $settings): float
    {
        $days = 0.0;

        if ($settings->deduct_absent_days) {
            $days += $this->absentDays($salesmanId, $monthStart, $monthEnd);
        }

        if ($settings->deduct_unpaid_leave) {
            $days += $this->unpaidLeaveDays($salesmanId, $monthStart, $monthEnd);
        }

        return round($days, 2);
    }

    /**
     * Working days in the month, per the configured basis.
     */
    public function workingDays(CarbonInterface $monthStart, HrmsSetting $settings): float
    {
        return $settings->working_days_basis === 'fixed'
            ? max(1, (float) $settings->fixed_working_days)
            : (float) $monthStart->daysInMonth;
    }

    private function absentDays(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        $logs = AttendanceLog::query()
            ->where('salesman_id', $salesmanId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereIn('status', ['absent', 'half_day'])
            ->get(['status']);

        return $logs->sum(static fn (AttendanceLog $log): float => $log->status === 'half_day' ? 0.5 : 1.0);
    }

    private function unpaidLeaveDays(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        $paidTypes = $this->leavePolicies->paidTypes();

        $leaves = LeaveApplication::query()
            ->where('salesman_id', $salesmanId)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $monthEnd)
            ->whereDate('to_date', '>=', $monthStart)
            ->when($paidTypes !== [], static fn ($query) => $query->whereNotIn('leave_type', $paidTypes))
            ->get(['from_date', 'to_date']);

        $days = 0.0;

        foreach ($leaves as $leave) {
            $from = $leave->from_date->greaterThan($monthStart) ? $leave->from_date : $monthStart;
            $to = $leave->to_date->lessThan($monthEnd) ? $leave->to_date : $monthEnd;
            $days += iterator_count(CarbonPeriod::create($from, $to));
        }

        return $days;
    }
}
