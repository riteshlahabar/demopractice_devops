<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Sales\Order;
use App\Services\Admin\Reports\Report;

final class OrderStatusReport extends Report
{
    public function key(): string
    {
        return 'order-status';
    }

    public function title(): string
    {
        return 'Order Status';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'How many orders, and how much value, sit at each stage from review to delivery.';
    }

    public function icon(): string
    {
        return 'list';
    }

    public function filters(): array
    {
        return ['date', 'channel'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $totals = $this->betweenDates(Order::query(), 'created_at', $filters)
            ->when($filters->channel, fn ($query, string $channel) => $query->where('order_type', $channel))
            ->selectRaw('status, COUNT(*) as orders, SUM(grand_total) as value')
            ->groupBy('status')->get()->keyBy('status');

        $labels = (array) config('admin.modules.orders.status_options', []);
        $allOrders = (int) $totals->sum('orders');

        // Every known stage is listed in workflow order, even at zero, then any
        // status the config does not know about.
        $statuses = array_unique(array_merge(array_keys($labels), $totals->keys()->all()));

        $rows = array_map(fn (string $status): array => [
            'status' => $labels[$status] ?? $status,
            'orders' => (int) ($totals[$status]->orders ?? 0),
            'share' => $this->percent((float) ($totals[$status]->orders ?? 0), $allOrders),
            'value' => (float) ($totals[$status]->value ?? 0),
        ], $statuses);

        return new ReportResult(
            cards: [
                ['label' => 'All Orders', 'icon' => 'shopping-cart', 'tone' => 'info', 'value' => $allOrders, 'type' => 'number'],
                ['label' => 'Delivered', 'icon' => 'check-circle', 'tone' => 'primary', 'value' => (int) ($totals['delivered']->orders ?? 0), 'type' => 'number'],
                ['label' => 'Cancelled', 'icon' => 'x-circle', 'tone' => 'danger', 'value' => (int) ($totals['cancelled']->orders ?? 0), 'type' => 'number'],
                ['label' => 'Value in Pipeline', 'icon' => 'truck', 'tone' => 'warning', 'value' => $totals->except(['delivered', 'cancelled'])->sum('value'), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'orders', 'label' => 'Orders', 'type' => 'number'],
                ['key' => 'share', 'label' => 'Share', 'type' => 'percent'],
                ['key' => 'value', 'label' => 'Value', 'type' => 'money'],
            ],
            rows: $rows,
        );
    }
}
