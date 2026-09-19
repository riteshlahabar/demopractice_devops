<?php

namespace App\Services\Hr\Payroll;

use App\Data\Hr\PayrollLine;
use App\Models\Hr\AdvanceInstallment;
use Carbon\CarbonInterface;

/**
 * Advance and loan EMIs falling due in the payroll month. Only installments
 * that are still pending are charged, and never more than what is left of the
 * advance, so a payslip cannot recover an advance twice.
 */
class AdvanceRecoveryCalculator
{
    /**
     * @return array<int, PayrollLine>
     */
    public function resolve(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        $installments = AdvanceInstallment::query()
            ->with('advance')
            ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereIn('status', ['pending', 'due', 'partial'])
            ->whereHas('advance', static fn ($query) => $query
                ->where('salesman_id', $salesmanId)
                ->whereIn('status', ['approved', 'disbursed', 'recovering'])
            )
            ->orderBy('due_date')
            ->get();

        $lines = [];

        foreach ($installments as $installment) {
            $advance = $installment->advance;

            if ($advance === null) {
                continue;
            }

            $outstanding = max(0, (float) $advance->amount - (float) $advance->recovered_amount);
            $due = max(0, (float) $installment->amount - (float) $installment->paid_amount);
            $amount = round(min($due, $outstanding), 2);

            if ($amount <= 0) {
                continue;
            }

            $label = trim(($advance->advance_type === 'loan' ? 'Loan' : 'Advance').' EMI '.$installment->installment_no);
            $lines[] = PayrollLine::deduction('advance', $installment->id, $label, $amount);
        }

        return $lines;
    }
}
