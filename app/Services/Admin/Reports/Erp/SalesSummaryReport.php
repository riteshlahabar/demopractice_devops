<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Sales\Order;
use App\Services\Admin\Reports\Report;

final class SalesSummaryReport extends Report
{
    public function key(): string
    {
        return 'sales-summary';
    }

    public function title(): string
    {
        return 'Sales Summary';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Orders and sales value per day or month, dealer vs customer. Cancelled orders are excluded.';
    }

    public function icon(): string
    {
        return 'trending-up';
    }

    public function filters(): array
    {
        return ['date', 'channel', 'period'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $orders = $this->betweenDates(Order::query(), 'created_at', $filters)
            ->where('status', '!=', 'cancelled')
            ->when($filters->channel, fn ($query, string $channel) => $query->where('order_type', $channel))
            ->get(['order_type', 'subtotal', 'gst_total', 'discount_total', 'grand_total', 'created_at']);

        $format = $filters->period === 'month' ? 'Y-m' : 'Y-m-d';

        $rows = $orders
            ->groupBy(fn (Order $order): string => $order->created_at->format($format))
            ->sortKeys()
            ->map(fn ($group, string $period): array => [
                'period' => $filters->period === 'month' ? date('M Y', strtotime($period.'-01')) : date('d-m-Y', strtotime($period)),
                'orders' => $group->count(),
                'dealer_sales' => $group->where('order_type', 'dealer')->sum('grand_total'),
                'customer_sales' => $group->where('order_type', 'customer')->sum('grand_total'),
                'subtotal' => $group->sum('subtotal'),
                'gst' => $group->sum('gst_total'),
                'discount' => $group->sum('discount_total'),
                'total' => $group->sum('grand_total'),
            ])
            ->values()
            ->all();

        $total = (float) $orders->sum('grand_total');

        return new ReportResult(
            cards: [
                ['label' => 'Orders', 'icon' => 'shopping-cart', 'tone' => 'info', 'value' => $orders->count(), 'type' => 'number'],
                ['label' => 'Total Sales', 'icon' => 'trending-up', 'tone' => 'primary', 'value' => $total, 'type' => 'money'],
                ['label' => 'GST Collected', 'icon' => 'percent', 'tone' => 'purple', 'value' => $orders->sum('gst_total'), 'type' => 'money'],
                ['label' => 'Average Order Value', 'icon' => 'activity', 'tone' => 'warning', 'value' => $orders->count() ? $total / $orders->count() : 0, 'type' => 'money'],
            ],
            columns: [
                ['key' => 'period', 'label' => $filters->period === 'month' ? 'Month' : 'Date'],
                ['key' => 'orders', 'label' => 'Orders', 'type' => 'number'],
                ['key' => 'dealer_sales', 'label' => 'Dealer Sales', 'type' => 'money'],
                ['key' => 'customer_sales', 'label' => 'Customer Sales', 'type' => 'money'],
                ['key' => 'subtotal', 'label' => 'Subtotal', 'type' => 'money'],
                ['key' => 'gst', 'label' => 'GST', 'type' => 'money'],
                ['key' => 'discount', 'label' => 'Discount', 'type' => 'money'],
                ['key' => 'total', 'label' => 'Grand Total', 'type' => 'money'],
            ],
            rows: $rows,
        );
    }
}
