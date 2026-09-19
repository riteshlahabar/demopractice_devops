<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\Expense;
use App\Services\Admin\Reports\Report;

final class ExpenseClaimReport extends Report
{
    public function key(): string
    {
        return 'expense-claims';
    }

    public function title(): string
    {
        return 'Expense Claims';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Salesman expense claims by type, split into approved, pending and rejected amounts.';
    }

    public function icon(): string
    {
        return 'file-text';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $groups = Expense::query()
            ->whereBetween('expense_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->selectRaw("salesman_id, expense_type, COUNT(*) as claims, SUM(amount) as claimed,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as rejected")
            ->groupBy('salesman_id', 'expense_type')
            ->get();

        $names = $this->userNames($groups->pluck('salesman_id'));

        $rows = $groups->map(fn (Expense $group): array => [
            'salesman' => $names[$group->salesman_id] ?? '#'.$group->salesman_id,
            'type' => $group->expense_type,
            'claims' => (int) $group->claims,
            'claimed' => (float) $group->claimed,
            'approved' => (float) $group->approved,
            'pending' => (float) $group->pending,
            'rejected' => (float) $group->rejected,
        ])->sortBy('salesman')->values()->all();

        return new ReportResult(
            cards: [
                ['label' => 'Claimed', 'icon' => 'file-text', 'tone' => 'info', 'value' => array_sum(array_column($rows, 'claimed')), 'type' => 'money'],
                ['label' => 'Approved', 'icon' => 'check-circle', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'approved')), 'type' => 'money'],
                ['label' => 'Pending', 'icon' => 'clock', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'pending')), 'type' => 'money'],
                ['label' => 'Rejected', 'icon' => 'x-circle', 'tone' => 'danger', 'value' => array_sum(array_column($rows, 'rejected')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'type', 'label' => 'Expense Type', 'type' => 'status'],
                ['key' => 'claims', 'label' => 'Claims', 'type' => 'number'],
                ['key' => 'claimed', 'label' => 'Claimed', 'type' => 'money'],
                ['key' => 'approved', 'label' => 'Approved', 'type' => 'money'],
                ['key' => 'pending', 'label' => 'Pending', 'type' => 'money'],
                ['key' => 'rejected', 'label' => 'Rejected', 'type' => 'money'],
            ],
            rows: $rows,
        );
    }
}
