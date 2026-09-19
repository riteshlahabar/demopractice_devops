<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\Admin\Reports\Report;

final class SalesmanSalesReport extends Report
{
    public function key(): string
    {
        return 'salesman-sales';
    }

    public function title(): string
    {
        return 'Salesman-wise Sales';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Orders booked, dealers served and money collected per salesman.';
    }

    public function icon(): string
    {
        return 'user-check';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $salesmen = User::query()->where('role', User::ROLE_SALESMAN)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->whereKey($id))
            ->orderBy('name')->get(['id', 'name']);

        $orders = $this->betweenDates(Order::query(), 'created_at', $filters)
            ->where('status', '!=', 'cancelled')->whereIn('salesman_id', $salesmen->pluck('id'))
            ->selectRaw('salesman_id, COUNT(*) as orders, COUNT(DISTINCT dealer_id) as dealers, SUM(grand_total) as sales')
            ->groupBy('salesman_id')->get()->keyBy('salesman_id');

        $collected = $this->betweenDates(Payment::query(), 'created_at', $filters)
            ->whereIn('status', self::RECEIVED_PAYMENT_STATUSES)->whereIn('collected_by', $salesmen->pluck('id'))
            ->selectRaw('collected_by, SUM(amount) as collected')
            ->groupBy('collected_by')->pluck('collected', 'collected_by');

        $rows = $salesmen->map(fn (User $salesman): array => [
            'salesman' => $salesman->name,
            'orders' => (int) ($orders[$salesman->id]->orders ?? 0),
            'dealers' => (int) ($orders[$salesman->id]->dealers ?? 0),
            'sales' => (float) ($orders[$salesman->id]->sales ?? 0),
            'collected' => (float) ($collected[$salesman->id] ?? 0),
        ])->sortByDesc('sales')->values()->all();

        return new ReportResult(
            cards: [
                ['label' => 'Salesmen', 'icon' => 'users', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Orders Booked', 'icon' => 'shopping-cart', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'orders')), 'type' => 'number'],
                ['label' => 'Sales', 'icon' => 'trending-up', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'sales')), 'type' => 'money'],
                ['label' => 'Collected', 'icon' => 'dollar-sign', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'collected')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'orders', 'label' => 'Orders', 'type' => 'number'],
                ['key' => 'dealers', 'label' => 'Dealers Served', 'type' => 'number'],
                ['key' => 'sales', 'label' => 'Sales', 'type' => 'money'],
                ['key' => 'collected', 'label' => 'Collected', 'type' => 'money'],
            ],
            rows: $rows,
        );
    }
}
