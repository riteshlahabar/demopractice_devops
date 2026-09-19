<?php

namespace App\Http\Controllers\Admin\Salary;

use App\Contracts\Hr\HrmsSettingsContract;
use App\Contracts\Hr\PayrollComputationContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalarySlipLine;
use App\Models\User;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryController extends AdminModuleController
{
    protected string $moduleKey = 'salary';

    public function __construct(
        AdminModuleServices $modules,
        private readonly PayrollComputationContract $payroll,
        private readonly HrmsSettingsContract $settings,
    ) {
        parent::__construct($modules);
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $gross = (float) ($data['basic_salary'] ?? $record?->basic_salary ?? 0)
            + (float) ($data['allowances'] ?? $record?->allowances ?? 0)
            + (float) ($data['bonus'] ?? $record?->bonus ?? 0)
            + (float) ($data['incentives'] ?? $record?->incentives ?? 0)
            + (float) ($data['commission'] ?? $record?->commission ?? 0);

        $data['gross_salary'] = round($gross, 2);
        $data['net_salary'] = $this->settings->current()->round(
            max(0, $gross - (float) ($data['deductions'] ?? $record?->deductions ?? 0))
        );

        return parent::persist($data, $record);
    }

    /**
     * Build a draft payslip per active salesman for the chosen month. Every
     * allowance, deduction, statutory contribution and advance EMI is computed
     * by PayrollComputationService and stored line by line, so the slip can be
     * explained even after the underlying masters change. A month that is
     * already approved or paid is left alone.
     */
    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'salary_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'salary_month' => ['required', 'integer', 'between:1,12'],
        ]);

        $generated = 0;
        $skipped = 0;

        User::with('salesmanProfile')
            ->where('role', User::ROLE_SALESMAN)
            ->where('status', 'active')
            ->chunkById(100, function ($salesmen) use ($data, &$generated, &$skipped): void {
                foreach ($salesmen as $salesman) {
                    $existing = SalarySlip::query()
                        ->where('salesman_id', $salesman->id)
                        ->where('salary_year', $data['salary_year'])
                        ->where('salary_month', $data['salary_month'])
                        ->first();

                    if ($existing !== null && $existing->status !== 'draft') {
                        $skipped++;

                        continue;
                    }

                    $this->storeSlip($salesman, $data, $existing);
                    $generated++;
                }
            });

        $message = "Salary generated for {$generated} salesmen.";

        if ($skipped > 0) {
            $message .= " {$skipped} already approved or paid and were left unchanged.";
        }

        return back()->with('success', $message);
    }

    /**
     * @param  array{salary_year: int, salary_month: int}  $period
     */
    private function storeSlip(User $salesman, array $period, ?SalarySlip $existing): void
    {
        $result = $this->payroll->compute($salesman, $period['salary_year'], $period['salary_month']);
        $columns = $result->toSlipColumns();
        $columns['net_salary'] = $this->settings->current()->round($result->netSalary());
        $columns['status'] = 'draft';

        DB::transaction(function () use ($salesman, $period, $existing, $result, $columns): void {
            $slip = $existing ?? new SalarySlip([
                'salesman_id' => $salesman->id,
                'salary_year' => $period['salary_year'],
                'salary_month' => $period['salary_month'],
            ]);
            $slip->fill($columns)->save();

            SalarySlipLine::query()->where('salary_slip_id', $slip->id)->delete();

            foreach ($result->lines as $line) {
                SalarySlipLine::query()->create($line->toRow() + ['salary_slip_id' => $slip->id]);
            }
        });
    }
}
