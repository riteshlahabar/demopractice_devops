<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Sales\ReturnRequest;
use App\Services\Admin\Reports\Report;
use Illuminate\Database\Eloquent\Builder;

final class ReturnsReport extends Report
{
    public function key(): string
    {
        return 'returns';
    }

    public function title(): string
    {
        return 'Returns';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Every return request raised in the period with its status and refund.';
    }

    public function icon(): string
    {
        return 'rotate-ccw';
    }

    public function filters(): array
    {
        return ['date', 'channel'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $returns = $this->betweenDates(ReturnRequest::query(), 'created_at', $filters)
            ->with(['order:id,order_no,order_type', 'user:id,name'])
            ->when($filters->channel, fn ($query, string $channel) => $query->whereHas('order', fn (Builder $order) => $order->where('order_type', $channel)))
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $returns->map(fn (ReturnRequest $return): array => [
            'return_no' => $return->return_no,
            'date' => $return->created_at,
            'order_no' => $return->order?->order_no,
            'channel' => $return->order?->order_type,
            'raised_by' => $return->user?->name,
            'reason' => $return->reason,
            'status' => $return->status,
            'refund' => (float) $return->refund_amount,
        ])->all();

        return new ReportResult(
            cards: [
                ['label' => 'Return Requests', 'icon' => 'rotate-ccw', 'tone' => 'info', 'value' => $returns->count(), 'type' => 'number'],
                ['label' => 'Pending', 'icon' => 'clock', 'tone' => 'warning', 'value' => $returns->whereIn('status', ['requested', 'approved', 'received'])->count(), 'type' => 'number'],
                ['label' => 'Rejected', 'icon' => 'x-circle', 'tone' => 'danger', 'value' => $returns->where('status', 'rejected')->count(), 'type' => 'number'],
                ['label' => 'Refunded Amount', 'icon' => 'dollar-sign', 'tone' => 'primary', 'value' => $returns->where('status', 'refunded')->sum('refund_amount'), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'return_no', 'label' => 'Return No.'],
                ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'order_no', 'label' => 'Order No.'],
                ['key' => 'channel', 'label' => 'Channel', 'type' => 'status'],
                ['key' => 'raised_by', 'label' => 'Raised By'],
                ['key' => 'reason', 'label' => 'Reason'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ['key' => 'refund', 'label' => 'Refund', 'type' => 'money'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
