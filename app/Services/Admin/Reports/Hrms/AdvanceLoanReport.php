<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Hr\SalaryAdvance;
use App\Services\Admin\Reports\Report;

final class AdvanceLoanReport extends Report
{
    public function key(): string
    {
        return 'advances';
    }

    public function title(): string
    {
        return 'Advances & Loans';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Advances and loans requested in the period: amount, recovered and still pending.';
    }

    public function icon(): string
    {
        return 'briefcase';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $advances = $this->betweenDates(SalaryAdvance::query(), 'created_at', $filters)
            ->with('salesman:id,name')
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $advances->map(fn (SalaryAdvance $advance): array => [
            'reference' => $advance->reference_no,
            'date' => $advance->created_at,
            'salesman' => $advance->salesman?->name,
            'type' => $advance->advance_type,
            'amount' => (float) $advance->amount,
            'emis' => (int) $advance->installments,
            'recovered' => (float) $advance->recovered_amount,
            'pending' => $advance->outstanding_amount,
            'status' => $advance->status,
        ])->all();

        $given = $advances->whereIn('status', ['approved', 'disbursed', 'closed']);

        return new ReportResult(
            cards: [
                ['label' => 'Requests', 'icon' => 'file-text', 'tone' => 'info', 'value' => $advances->count(), 'type' => 'number'],
                ['label' => 'Sanctioned', 'icon' => 'credit-card', 'tone' => 'primary', 'value' => $given->sum('amount'), 'type' => 'money'],
                ['label' => 'Recovered', 'icon' => 'check-circle', 'tone' => 'purple', 'value' => $given->sum('recovered_amount'), 'type' => 'money'],
                ['label' => 'Pending Recovery', 'icon' => 'clock', 'tone' => 'warning', 'value' => $given->sum(fn (SalaryAdvance $advance): float => $advance->outstanding_amount), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'reference', 'label' => 'Reference'],
                ['key' => 'date', 'label' => 'Requested', 'type' => 'date'],
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'type', 'label' => 'Type', 'type' => 'status'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['key' => 'emis', 'label' => 'EMIs', 'type' => 'number'],
                ['key' => 'recovered', 'label' => 'Recovered', 'type' => 'money'],
                ['key' => 'pending', 'label' => 'Pending', 'type' => 'money'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
