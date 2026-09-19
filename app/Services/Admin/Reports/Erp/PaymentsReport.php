<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Finance\Payment;
use App\Services\Admin\Reports\Report;

final class PaymentsReport extends Report
{
    public function key(): string
    {
        return 'payments';
    }

    public function title(): string
    {
        return 'Payments & Collections';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Payments by mode and status, including cash collected by salesmen.';
    }

    public function icon(): string
    {
        return 'credit-card';
    }

    public function filters(): array
    {
        return ['date', 'salesman', 'dealer'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $groups = $this->betweenDates(Payment::query(), 'created_at', $filters)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('collected_by', $id))
            ->when($filters->dealerId, fn ($query, int $id) => $query->where('payer_id', $id))
            ->selectRaw('payment_mode, status, COUNT(*) as payments, SUM(amount) as amount, SUM(CASE WHEN collected_by IS NULL THEN 0 ELSE amount END) as by_salesman')
            ->groupBy('payment_mode', 'status')
            ->orderBy('payment_mode')->orderBy('status')
            ->get();

        $rows = $groups->map(fn (Payment $group): array => [
            'mode' => $group->payment_mode ?: 'Not set',
            'status' => $group->status,
            'payments' => (int) $group->payments,
            'by_salesman' => (float) $group->by_salesman,
            'amount' => (float) $group->amount,
        ])->all();

        $received = $groups->whereIn('status', self::RECEIVED_PAYMENT_STATUSES);

        return new ReportResult(
            cards: [
                ['label' => 'Received', 'icon' => 'check-circle', 'tone' => 'primary', 'value' => $received->sum('amount'), 'type' => 'money'],
                ['label' => 'Collected by Salesmen', 'icon' => 'user-check', 'tone' => 'info', 'value' => $received->sum('by_salesman'), 'type' => 'money'],
                ['label' => 'Pending', 'icon' => 'clock', 'tone' => 'warning', 'value' => $groups->where('status', 'pending')->sum('amount'), 'type' => 'money'],
                ['label' => 'Failed / Refunded', 'icon' => 'x-circle', 'tone' => 'danger', 'value' => $groups->whereIn('status', ['failed', 'refunded'])->sum('amount'), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'mode', 'label' => 'Payment Mode', 'type' => 'status'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ['key' => 'payments', 'label' => 'Payments', 'type' => 'number'],
                ['key' => 'by_salesman', 'label' => 'Collected by Salesmen', 'type' => 'money'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
            ],
            rows: $rows,
        );
    }
}
