<?php

namespace App\Services\Hr\Payroll;

use App\Data\Hr\PayrollLine;
use App\Models\Hr\AllowanceType;
use App\Models\Hr\EmployeeAllowance;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Every allowance that applies to one salesman in one payroll month: the ones
 * assigned to them personally, plus any type marked "applies to all" that they
 * have no personal row for.
 */
class EmployeeAllowanceResolver
{
    /**
     * @return array<int, PayrollLine>
     */
    public function resolve(int $salesmanId, float $basic, CarbonInterface $monthEnd): array
    {
        $assigned = EmployeeAllowance::query()
            ->with('allowanceType')
            ->where('salesman_id', $salesmanId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $monthEnd)
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $monthEnd->copy()->startOfMonth()))
            ->get();

        $lines = [];
        $covered = [];

        foreach ($assigned as $allowance) {
            $type = $allowance->allowanceType;

            if ($type === null || ! $type->is_active) {
                continue;
            }

            $covered[] = $type->id;
            $amount = $type->amountFor(
                $basic,
                $allowance->calculation_type,
                $allowance->amount === null ? null : (float) $allowance->amount
            );

            if ($amount > 0) {
                $lines[] = PayrollLine::allowance('allowance_type', $type->id, $type->name, $amount);
            }
        }

        foreach ($this->blanketTypes($covered) as $type) {
            $amount = $type->amountFor($basic);

            if ($amount > 0) {
                $lines[] = PayrollLine::allowance('allowance_type', $type->id, $type->name, $amount);
            }
        }

        return $lines;
    }

    /**
     * @param  array<int, int>  $excludedIds
     * @return Collection<int, AllowanceType>
     */
    private function blanketTypes(array $excludedIds)
    {
        return AllowanceType::query()
            ->where('is_active', true)
            ->where('applies_to_all', true)
            ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->orderBy('sort_order')
            ->get();
    }
}
