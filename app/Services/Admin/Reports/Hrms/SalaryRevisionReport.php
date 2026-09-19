<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Hr\SalaryRevision;
use App\Services\Admin\Reports\Report;

final class SalaryRevisionReport extends Report
{
    public function key(): string
    {
        return 'salary-revisions';
    }

    public function title(): string
    {
        return 'Salary Revision';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Every change of basic salary effective in the date range, with the old and new amount and who revised it.';
    }

    public function icon(): string
    {
        return 'trending-up';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $revisions = SalaryRevision::query()
            ->with('salesman:id,name', 'reviser:id,name')
            ->whereBetween('effective_from', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->orderByDesc('effective_from')
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $revisions->map(fn (SalaryRevision $revision): array => [
            'salesman' => $revision->salesman?->name,
            'effective_from' => $revision->effective_from,
            'previous' => (float) $revision->previous_basic,
            'new' => (float) $revision->new_basic,
            'change' => $revision->change_amount,
            'change_percent' => $revision->change_percent,
            'reason' => $revision->reason_label,
            'reviser' => $revision->reviser?->name,
        ])->all();

        $increases = array_filter(array_column($rows, 'change'), static fn (float $change): bool => $change > 0);

        return new ReportResult(
            cards: [
                ['label' => 'Revisions', 'icon' => 'edit-3', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Employees', 'icon' => 'users', 'tone' => 'primary', 'value' => count(array_unique(array_column($rows, 'salesman'))), 'type' => 'number'],
                ['label' => 'Total Increase', 'icon' => 'trending-up', 'tone' => 'purple', 'value' => array_sum($increases), 'type' => 'money'],
                ['label' => 'Average Increase', 'icon' => 'percent', 'tone' => 'warning', 'value' => $increases === [] ? 0 : round(array_sum($increases) / count($increases), 2), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'effective_from', 'label' => 'Effective From', 'type' => 'date'],
                ['key' => 'previous', 'label' => 'Previous Basic', 'type' => 'money'],
                ['key' => 'new', 'label' => 'New Basic', 'type' => 'money'],
                ['key' => 'change', 'label' => 'Change', 'type' => 'money'],
                ['key' => 'change_percent', 'label' => 'Change %', 'type' => 'percent'],
                ['key' => 'reason', 'label' => 'Reason'],
                ['key' => 'reviser', 'label' => 'Revised By'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
