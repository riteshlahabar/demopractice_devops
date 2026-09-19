<?php

namespace App\Services\Hr;

use App\Contracts\Hr\EmployeeExitContract;
use App\Contracts\Hr\LeavePolicyContract;
use App\Models\Field\LeaveApplication;
use App\Models\Field\SalarySlip;
use App\Models\Hr\Resignation;
use App\Models\Hr\SalaryAdvance;
use App\Models\SalesmanProfile;
use Throwable;

/**
 * The money and the record-keeping side of an employee exit. The figures are
 * a *suggestion* the admin can overwrite — payroll disputes are settled by
 * people, not by a formula — but they are computed from the same data payroll
 * uses so the starting point is never invented.
 */
class EmployeeExitService implements EmployeeExitContract
{
    public function __construct(private readonly LeavePolicyContract $leavePolicies) {}

    public function settlementSuggestion(Resignation $resignation): array
    {
        return [
            'pending_salary' => $this->pendingSalary($resignation),
            'leave_encashment' => $this->leaveEncashment($resignation),
            'advance_recovery' => $this->advanceOutstanding($resignation),
        ];
    }

    public function syncEmploymentStatus(Resignation $resignation): void
    {
        try {
            $profile = SalesmanProfile::query()->where('user_id', $resignation->salesman_id)->first();

            if ($profile === null) {
                return;
            }

            $status = match ($resignation->status) {
                'pending', 'approved' => 'notice_period',
                'completed' => 'exited',
                'rejected', 'withdrawn' => 'active',
                default => null,
            };

            if ($status === null) {
                return;
            }

            $profile->employment_status = $status;
            $profile->exit_date = $resignation->status === 'completed'
                ? ($resignation->approved_last_working_date ?? $resignation->requested_last_working_date)
                : null;
            $profile->save();
        } catch (Throwable) {
            // Keeping the profile in step is a convenience; it must never stop
            // the resignation itself from being saved.
        }
    }

    /**
     * Approved payslips that have not been marked paid.
     */
    private function pendingSalary(Resignation $resignation): float
    {
        return (float) SalarySlip::query()
            ->where('salesman_id', $resignation->salesman_id)
            ->where('status', 'approved')
            ->sum('net_salary');
    }

    /**
     * Unused paid leave for the resignation year, valued at one day of basic
     * salary using a 30-day month.
     */
    private function leaveEncashment(Resignation $resignation): float
    {
        $year = ($resignation->resignation_date ?? now())->year;
        $entitlements = $this->leavePolicies->entitlements();
        $paidTypes = $this->leavePolicies->paidTypes();

        $entitled = 0.0;
        foreach ($paidTypes as $type) {
            $entitled += (float) ($entitlements[$type] ?? 0);
        }

        $taken = LeaveApplication::query()
            ->where('salesman_id', $resignation->salesman_id)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->when($paidTypes !== [], static fn ($query) => $query->whereIn('leave_type', $paidTypes))
            ->get()
            ->sum(static fn (LeaveApplication $leave): int => $leave->from_date->diffInDays($leave->to_date) + 1);

        $unused = max(0, $entitled - $taken);
        $basic = (float) (SalesmanProfile::query()
            ->where('user_id', $resignation->salesman_id)
            ->value('basic_salary') ?? 0);

        return round($unused * $basic / 30, 2);
    }

    /**
     * What is still owed on every advance and loan that has not been closed.
     */
    private function advanceOutstanding(Resignation $resignation): float
    {
        return (float) SalaryAdvance::query()
            ->where('salesman_id', $resignation->salesman_id)
            ->whereIn('status', ['approved', 'disbursed'])
            ->get()
            ->sum(static fn (SalaryAdvance $advance): float => max(0, (float) $advance->amount - (float) $advance->recovered_amount));
    }
}
