<?php

namespace App\Services\Hr;

use App\Contracts\Support\TransactionManagerContract;
use App\Models\Hr\SalaryAdvance;
use App\Models\User;
use RuntimeException;

/**
 * Creates advance/loan requests and their EMI schedule.
 *
 * The eligibility rules and the schedule maths live here rather than in the
 * controller so that the API, the admin panel and any future payroll job all
 * produce identical rows.
 */
class SalaryAdvanceService
{
    public function __construct(private readonly TransactionManagerContract $transactions) {}

    public function request(User $salesman, array $input): SalaryAdvance
    {
        $type = $input['advance_type'];

        $openCount = SalaryAdvance::query()
            ->where('salesman_id', $salesman->id)
            ->whereIn('status', ['pending', 'approved', 'disbursed'])
            ->whereColumn('recovered_amount', '<', 'amount')
            ->count();

        $maxOpen = (int) config('hrms.max_open_advances', 2);

        if ($openCount >= $maxOpen) {
            throw new RuntimeException(
                'You already have '.$maxOpen.' open requests. Clear one before raising another.'
            );
        }

        $amount = round((float) $input['amount'], 2);
        // An advance is recovered from the next salary in one go; only a loan
        // is spread over instalments.
        $installments = $type === SalaryAdvance::TYPE_LOAN
            ? max((int) ($input['installments'] ?? 1), 1)
            : 1;
        $emi = round($amount / $installments, 2);

        return $this->transactions->run(function () use ($salesman, $type, $amount, $installments, $emi, $input): SalaryAdvance {
            $advance = SalaryAdvance::query()->create([
                'salesman_id' => $salesman->id,
                'advance_type' => $type,
                'reference_no' => strtoupper(substr($type, 0, 3)).now()->format('ymdHis').random_int(10, 99),
                'amount' => $amount,
                'installments' => $installments,
                'emi_amount' => $emi,
                'reason' => $input['reason'] ?? null,
                'status' => 'pending',
            ]);

            $this->buildSchedule($advance, $amount, $installments, $emi);

            return $advance;
        });
    }

    /**
     * Writes one row per instalment. The last one absorbs any rounding
     * remainder so the schedule always sums to exactly the sanctioned amount.
     */
    private function buildSchedule(SalaryAdvance $advance, float $amount, int $installments, float $emi): void
    {
        $allocated = 0.0;

        for ($number = 1; $number <= $installments; $number++) {
            $isLast = $number === $installments;
            $due = $isLast ? round($amount - $allocated, 2) : $emi;
            $allocated += $due;

            $advance->schedule()->create([
                'installment_no' => $number,
                'due_date' => now()->addMonthsNoOverflow($number)->startOfMonth(),
                'amount' => $due,
                'status' => 'pending',
            ]);
        }
    }
}
