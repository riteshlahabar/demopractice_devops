<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Sales\OrderItem;
use App\Services\Admin\Reports\Report;
use Illuminate\Database\Eloquent\Builder;

final class ProductSalesReport extends Report
{
    public function key(): string
    {
        return 'product-sales';
    }

    public function title(): string
    {
        return 'Product-wise Sales';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Quantity and value sold per product, best sellers first.';
    }

    public function icon(): string
    {
        return 'package';
    }

    public function filters(): array
    {
        return ['date', 'channel'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $rows = OrderItem::query()
            ->whereHas('order', function (Builder $order) use ($filters): void {
                $this->betweenDates($order, 'created_at', $filters)->where('status', '!=', 'cancelled');

                if ($filters->channel) {
                    $order->where('order_type', $filters->channel);
                }
            })
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(line_total) as sales_value, SUM(gst_amount) as gst, COUNT(DISTINCT order_id) as orders')
            ->groupBy('product_id')
            ->orderByDesc('sales_value')
            ->limit(self::MAX_ROWS)
            ->with('product:id,name,sku')
            ->get()
            ->map(fn (OrderItem $item): array => [
                'product' => $item->product?->name ?? 'Deleted product #'.$item->product_id,
                'sku' => $item->product?->sku,
                'orders' => (int) $item->orders,
                'quantity' => (float) $item->quantity,
                'gst' => (float) $item->gst,
                'value' => (float) $item->sales_value,
            ])
            ->all();

        return new ReportResult(
            cards: [
                ['label' => 'Products Sold', 'icon' => 'package', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Quantity Sold', 'icon' => 'layers', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'quantity')), 'type' => 'number'],
                ['label' => 'Sales Value', 'icon' => 'trending-up', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'value')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'product', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'orders', 'label' => 'Orders', 'type' => 'number'],
                ['key' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
                ['key' => 'gst', 'label' => 'GST', 'type' => 'money'],
                ['key' => 'value', 'label' => 'Sales Value', 'type' => 'money'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
