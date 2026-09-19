<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\SalesmanTarget;
use App\Services\Admin\Reports\Report;

final class TargetCommissionReport extends Report
{
    public function key(): string
    {
        return 'targets';
    }

    public function title(): string
    {
        return 'Targets & Commission';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Target periods overlapping the date range: target, achieved, and commission earned on the achieved amount.';
    }

    public function icon(): string
    {
        return 'target';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $rows = SalesmanTarget::query()
            ->with('salesman:id,name')
            ->whereDate('period_start', '<=', $filters->to)
            ->whereDate('period_end', '>=', $filters->from)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->orderByDesc('period_start')
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(function (SalesmanTarget $target): array {
                $achieved = (float) $target->achieved_amount;

                return [
                    'salesman' => $target->salesman?->name,
                    'from' => $target->period_start,
                    'to' => $target->period_end,
                    'target' => (float) $target->target_amount,
                    'achieved' => $achieved,
                    'achievement' => $this->percent($achieved, (float) $target->target_amount),
                    'commission_rate' => (float) $target->commission_percent,
                    'commission' => round($achieved * (float) $target->commission_percent / 100, 2),
                ];
            })
            ->all();

        $target = array_sum(array_column($rows, 'target'));
        $achieved = array_sum(array_column($rows, 'achieved'));

        return new ReportResult(
            cards: [
                ['label' => 'Target', 'icon' => 'target', 'tone' => 'info', 'value' => $target, 'type' => 'money'],
                ['label' => 'Achieved', 'icon' => 'award', 'tone' => 'primary', 'value' => $achieved, 'type' => 'money'],
                ['label' => 'Achievement', 'icon' => 'percent', 'tone' => 'purple', 'value' => $this->percent($achieved, $target), 'type' => 'percent'],
                ['label' => 'Commission Earned', 'icon' => 'dollar-sign', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'commission')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'from', 'label' => 'From', 'type' => 'date'],
                ['key' => 'to', 'label' => 'To', 'type' => 'date'],
                ['key' => 'target', 'label' => 'Target', 'type' => 'money'],
                ['key' => 'achieved', 'label' => 'Achieved', 'type' => 'money'],
                ['key' => 'achievement', 'label' => 'Achievement', 'type' => 'percent'],
                ['key' => 'commission_rate', 'label' => 'Commission %', 'type' => 'percent'],
                ['key' => 'commission', 'label' => 'Commission', 'type' => 'money'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
