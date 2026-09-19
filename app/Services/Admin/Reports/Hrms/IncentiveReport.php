<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\SalarySlip;
use App\Services\Admin\Reports\Report;

final class IncentiveReport extends Report
{
    public function key(): string
    {
        return 'incentives';
    }

    public function title(): string
    {
        return 'Incentive';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Incentive and commission actually paid through payroll, month by month, from the incentive and commission rules.';
    }

    public function icon(): string
    {
        return 'gift';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        // Payroll is what pays an incentive, so the slip is the record of it;
        // the rules only say how the figure was arrived at.
        $fromMonth = (int) $filters->from->format('Ym');
        $toMonth = (int) $filters->to->format('Ym');

        $slips = SalarySlip::query()
            ->with('salesman:id,name')
            ->whereRaw('(salary_year * 100 + salary_month) BETWEEN ? AND ?', [$fromMonth, $toMonth])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->where(function ($query): void {
                $query->where('incentives', '>', 0)->orWhere('commission', '>', 0);
            })
            ->orderByDesc('salary_year')->orderByDesc('salary_month')
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $slips->map(function (SalarySlip $slip): array {
            $incentive = (float) $slip->incentives;
            $commission = (float) $slip->commission;

            return [
                'salesman' => $slip->salesman?->name,
                'month' => date('M Y', mktime(0, 0, 0, (int) $slip->salary_month, 1, (int) $slip->salary_year)),
                'incentive' => $incentive,
                'commission' => $commission,
                'total' => round($incentive + $commission, 2),
                'gross' => (float) $slip->gross_salary,
                'share' => $this->percent($incentive + $commission, (float) $slip->gross_salary),
                'status' => $slip->status,
            ];
        })->all();

        return new ReportResult(
            cards: [
                ['label' => 'Payouts', 'icon' => 'file-text', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Incentive', 'icon' => 'gift', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'incentive')), 'type' => 'money'],
                ['label' => 'Commission', 'icon' => 'percent', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'commission')), 'type' => 'money'],
                ['label' => 'Total Paid', 'icon' => 'dollar-sign', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'total')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'month', 'label' => 'Month'],
                ['key' => 'incentive', 'label' => 'Incentive', 'type' => 'money'],
                ['key' => 'commission', 'label' => 'Commission', 'type' => 'money'],
                ['key' => 'total', 'label' => 'Total', 'type' => 'money'],
                ['key' => 'gross', 'label' => 'Gross Salary', 'type' => 'money'],
                ['key' => 'share', 'label' => 'Share of Gross', 'type' => 'percent'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
