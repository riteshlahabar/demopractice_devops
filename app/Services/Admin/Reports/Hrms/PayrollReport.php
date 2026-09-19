<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\SalarySlip;
use App\Services\Admin\Reports\Report;

final class PayrollReport extends Report
{
    public function key(): string
    {
        return 'payroll';
    }

    public function title(): string
    {
        return 'Payroll';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Salary slips for the months in the period: earnings, deductions and net pay.';
    }

    public function icon(): string
    {
        return 'dollar-sign';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        // Slips are stored by year and month, so compare as YYYYMM numbers.
        $fromMonth = (int) $filters->from->format('Ym');
        $toMonth = (int) $filters->to->format('Ym');

        $slips = SalarySlip::query()
            ->with('salesman:id,name')
            ->whereRaw('(salary_year * 100 + salary_month) BETWEEN ? AND ?', [$fromMonth, $toMonth])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->orderByDesc('salary_year')->orderByDesc('salary_month')
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $slips->map(fn (SalarySlip $slip): array => [
            'salesman' => $slip->salesman?->name,
            'month' => date('M Y', mktime(0, 0, 0, (int) $slip->salary_month, 1, (int) $slip->salary_year)),
            'basic' => (float) $slip->basic_salary,
            'allowances' => (float) $slip->allowances,
            'bonus' => (float) $slip->bonus,
            'incentives' => (float) $slip->incentives + (float) $slip->commission,
            'deductions' => (float) $slip->deductions,
            'net' => (float) $slip->net_salary,
            'status' => $slip->status,
        ])->all();

        return new ReportResult(
            cards: [
                ['label' => 'Salary Slips', 'icon' => 'file-text', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Gross Earnings', 'icon' => 'trending-up', 'tone' => 'primary', 'value' => array_sum(array_map(fn (array $row): float => $row['basic'] + $row['allowances'] + $row['bonus'] + $row['incentives'], $rows)), 'type' => 'money'],
                ['label' => 'Deductions', 'icon' => 'minus-circle', 'tone' => 'danger', 'value' => array_sum(array_column($rows, 'deductions')), 'type' => 'money'],
                ['label' => 'Net Payable', 'icon' => 'dollar-sign', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'net')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'month', 'label' => 'Month'],
                ['key' => 'basic', 'label' => 'Basic', 'type' => 'money'],
                ['key' => 'allowances', 'label' => 'Allowances', 'type' => 'money'],
                ['key' => 'bonus', 'label' => 'Bonus', 'type' => 'money'],
                ['key' => 'incentives', 'label' => 'Incentive + Commission', 'type' => 'money'],
                ['key' => 'deductions', 'label' => 'Deductions', 'type' => 'money'],
                ['key' => 'net', 'label' => 'Net Salary', 'type' => 'money'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
