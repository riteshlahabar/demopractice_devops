<?php

namespace App\Services\Hr\Payroll;

use App\Data\Hr\PayrollLine;
use App\Models\Hr\DeductionType;
use App\Models\Hr\EmployeeDeduction;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Every deduction that applies to one salesman in one payroll month. Statutory
 * rows (PF/ESI/PT) are handed to StatutoryDeductionCalculator; the rest use the
 * plain fixed or percentage formula. Returns the lines and the employer share,
 * which is reported on the payslip but not taken out of net pay.
 */
class EmployeeDeductionResolver
{
    public function __construct(private readonly StatutoryDeductionCalculator $statutory) {}

    /**
     * @return array{lines: array<int, PayrollLine>, employer_contribution: float}
     */
    public function resolve(int $salesmanId, float $basic, float $gross, CarbonInterface $monthEnd): array
    {
        $assigned = EmployeeDeduction::query()
            ->with('deductionType')
            ->where('salesman_id', $salesmanId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $monthEnd)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $monthEnd->copy()->startOfMonth()))
            ->get();

        $lines = [];
        $employer = 0.0;
        $covered = [];

        foreach ($assigned as $deduction) {
            $type = $deduction->deductionType;

            if ($type === null || ! $type->is_active) {
                continue;
            }

            $covered[] = $type->id;
            [$amount, $employerShare] = $this->amountFor(
                $type,
                $basic,
                $gross,
                $deduction->calculation_type,
                $deduction->amount === null ? null : (float) $deduction->amount
            );
            $employer += $employerShare;

            if ($amount > 0) {
                $lines[] = PayrollLine::deduction('deduction_type', $type->id, $type->name, $amount);
            }
        }

        foreach ($this->blanketTypes($covered) as $type) {
            [$amount, $employerShare] = $this->amountFor($type, $basic, $gross);
            $employer += $employerShare;

            if ($amount > 0) {
                $lines[] = PayrollLine::deduction('deduction_type', $type->id, $type->name, $amount);
            }
        }

        return ['lines' => $lines, 'employer_contribution' => round($employer, 2)];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function amountFor(DeductionType $type, float $basic, float $gross, ?string $calculation = null, ?float $value = null): array
    {
        if ($type->isStatutory()) {
            return [
                $this->statutory->employeeShare($type, $basic, $gross),
                $this->statutory->employerShare($type, $basic, $gross),
            ];
        }

        $calculation ??= $type->calculation_type;
        $value ??= (float) $type->default_value;

        $amount = match ($calculation) {
            'percent_of_basic' => $basic * $value / 100,
            'percent_of_gross' => $gross * $value / 100,
            default => $value,
        };

        return [round(max(0, $amount), 2), 0.0];
    }

    /**
     * @param  array<int, int>  $excludedIds
     * @return Collection<int, DeductionType>
     */
    private function blanketTypes(array $excludedIds)
    {
        return DeductionType::query()
            ->where('is_active', true)
            ->where('applies_to_all', true)
            ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->orderBy('sort_order')
            ->get();
    }
}
