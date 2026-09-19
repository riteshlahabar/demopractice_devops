<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Sales\OrderItem;
use App\Services\Admin\Reports\Report;
use Illuminate\Database\Eloquent\Builder;

final class GstSummaryReport extends Report
{
    public function key(): string
    {
        return 'gst-summary';
    }

    public function title(): string
    {
        return 'GST Summary';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Taxable value and GST by rate from order lines (line totals include GST). Cancelled orders are excluded. Check figures with your accountant before filing.';
    }

    public function icon(): string
    {
        return 'percent';
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
            ->selectRaw('gst_percent, COUNT(*) as line_count, SUM(line_total) as gross, SUM(gst_amount) as gst')
            ->groupBy('gst_percent')
            ->orderBy('gst_percent')
            ->get()
            ->map(fn (OrderItem $group): array => [
                'rate' => (float) $group->gst_percent,
                'lines' => (int) $group->line_count,
                'taxable' => (float) $group->gross - (float) $group->gst,
                'cgst' => round((float) $group->gst / 2, 2),
                'sgst' => round((float) $group->gst / 2, 2),
                'gst' => (float) $group->gst,
                'gross' => (float) $group->gross,
            ])
            ->all();

        return new ReportResult(
            cards: [
                ['label' => 'Taxable Value', 'icon' => 'file-text', 'tone' => 'info', 'value' => array_sum(array_column($rows, 'taxable')), 'type' => 'money'],
                ['label' => 'Total GST', 'icon' => 'percent', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'gst')), 'type' => 'money'],
                ['label' => 'Gross Value', 'icon' => 'trending-up', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'gross')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'rate', 'label' => 'GST Rate', 'type' => 'percent'],
                ['key' => 'lines', 'label' => 'Order Lines', 'type' => 'number'],
                ['key' => 'taxable', 'label' => 'Taxable Value', 'type' => 'money'],
                ['key' => 'cgst', 'label' => 'CGST (half)', 'type' => 'money'],
                ['key' => 'sgst', 'label' => 'SGST (half)', 'type' => 'money'],
                ['key' => 'gst', 'label' => 'Total GST', 'type' => 'money'],
                ['key' => 'gross', 'label' => 'Gross Value', 'type' => 'money'],
            ],
            rows: $rows,
            note: 'CGST/SGST are shown as half of total GST; inter-state (IGST) orders are not separated here.',
        );
    }
}
