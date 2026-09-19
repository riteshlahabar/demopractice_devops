<?php

namespace App\Data\Hr;

/**
 * The computed payroll for one salesman for one month: the money totals that
 * go on `salary_slips`, plus the lines that explain them.
 */
final class PayrollResult
{
    /**
     * @param  array<int, PayrollLine>  $lines
     */
    public function __construct(
        public readonly float $basicSalary,
        public readonly float $allowances,
        public readonly float $deductions,
        public readonly float $employerContribution,
        public readonly float $workingDays,
        public readonly float $payableDays,
        public readonly array $lines,
        public readonly float $incentives = 0.0,
        public readonly float $commission = 0.0,
    ) {}

    /**
     * Incentive and commission are earnings, so they are part of gross and
     * therefore part of what percentage-of-gross deductions are charged on.
     */
    public function grossSalary(): float
    {
        return round($this->basicSalary + $this->allowances + $this->incentives + $this->commission, 2);
    }

    public function netSalary(): float
    {
        return round(max(0, $this->grossSalary() - $this->deductions), 2);
    }

    /**
     * @return array<int, PayrollLine>
     */
    public function linesOf(string $kind): array
    {
        return array_values(array_filter($this->lines, static fn (PayrollLine $line): bool => $line->kind === $kind));
    }

    /**
     * Column values for the `salary_slips` row.
     *
     * @return array<string, mixed>
     */
    public function toSlipColumns(): array
    {
        return [
            'basic_salary' => round($this->basicSalary, 2),
            'allowances' => round($this->allowances, 2),
            'incentives' => round($this->incentives, 2),
            'commission' => round($this->commission, 2),
            'gross_salary' => $this->grossSalary(),
            'deductions' => round($this->deductions, 2),
            'employer_contribution' => round($this->employerContribution, 2),
            'working_days' => $this->workingDays,
            'payable_days' => $this->payableDays,
            'net_salary' => $this->netSalary(),
        ];
    }
}
