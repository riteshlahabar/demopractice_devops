<?php

namespace App\Services\Hr\Payroll;

use App\Contracts\Hr\HrmsSettingsContract;
use App\Contracts\Hr\IncentiveCalculationContract;
use App\Contracts\Hr\PayrollComputationContract;
use App\Data\Hr\PayrollLine;
use App\Data\Hr\PayrollResult;
use App\Models\User;
use Carbon\Carbon;

/**
 * Puts one salesman's month together: basic salary pro-rated for unpaid days,
 * then allowances, then deductions (which need the gross, so they run second),
 * then advance recovery.
 */
class PayrollComputationService implements PayrollComputationContract
{
    public function __construct(
        private readonly HrmsSettingsContract $settings,
        private readonly EmployeeAllowanceResolver $allowances,
        private readonly EmployeeDeductionResolver $deductions,
        private readonly AdvanceRecoveryCalculator $advances,
        private readonly AttendanceDeductionCalculator $attendance,
        private readonly IncentiveCalculationContract $incentives,
    ) {}

    public function compute(User $salesman, int $year, int $month): PayrollResult
    {
        $settings = $this->settings->current();
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $contracted = (float) ($salesman->salesmanProfile?->basic_salary ?? 0);
        $workingDays = $this->attendance->workingDays($monthStart, $settings);
        $unpaidDays = min($workingDays, $this->attendance->unpaidDays($salesman->id, $monthStart, $monthEnd, $settings));
        $payableDays = round($workingDays - $unpaidDays, 2);

        $basic = $workingDays > 0 ? round($contracted * $payableDays / $workingDays, 2) : 0.0;

        $allowanceLines = $this->allowances->resolve($salesman->id, $basic, $monthEnd);
        $allowanceTotal = $this->sum($allowanceLines);

        $incentive = $this->incentives->incentiveFor($salesman->id, $monthStart, $monthEnd);
        $commission = $this->incentives->commissionFor($salesman->id, $monthStart, $monthEnd);

        // Incentive and commission are earnings, so deductions charged as a
        // percentage of gross must see them.
        $gross = round($basic + $allowanceTotal + $incentive + $commission, 2);

        // Unpaid days are already taken out by pro-rating basic above, so loss
        // of pay is never a deduction line — working_days / payable_days on the
        // slip is what explains the smaller basic.
        $resolved = $this->deductions->resolve($salesman->id, $basic, $gross, $monthEnd);
        $deductionLines = array_merge(
            $resolved['lines'],
            $this->advances->resolve($salesman->id, $monthStart, $monthEnd)
        );

        $deductionTotal = $this->sum($deductionLines);

        return new PayrollResult(
            basicSalary: $basic,
            allowances: $allowanceTotal,
            deductions: $deductionTotal,
            employerContribution: $resolved['employer_contribution'],
            workingDays: $workingDays,
            payableDays: $payableDays,
            lines: array_merge($allowanceLines, $deductionLines),
            incentives: $incentive,
            commission: $commission,
        );
    }

    /**
     * @param  array<int, PayrollLine>  $lines
     */
    private function sum(array $lines): float
    {
        return round(array_sum(array_map(static fn (PayrollLine $line): float => $line->amount, $lines)), 2);
    }
}
