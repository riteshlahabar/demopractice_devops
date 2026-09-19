<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Inventory\InventoryBatch;
use App\Services\Admin\Reports\Report;
use Illuminate\Support\Carbon;

final class StockExpiryReport extends Report
{
    public function key(): string
    {
        return 'stock-expiry';
    }

    public function title(): string
    {
        return 'Stock & Expiry';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Stock per batch and warehouse, with low stock, expired and soon-to-expire batches flagged. Not date based.';
    }

    public function icon(): string
    {
        return 'archive';
    }

    public function filters(): array
    {
        return ['warehouse', 'expiry_days'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $today = today();
        $window = $filters->expiryDays;

        $rows = InventoryBatch::query()
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->when($filters->warehouseId, fn ($query, int $id) => $query->where('warehouse_id', $id))
            ->when($window !== null, fn ($query) => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', $today->copy()->addDays($window)))
            ->orderByRaw('expiry_date IS NULL')->orderBy('expiry_date')
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(function (InventoryBatch $batch) use ($today): array {
                $available = (float) $batch->quantity - (float) $batch->reserved_quantity;
                $expiry = $batch->expiry_date ? Carbon::parse($batch->expiry_date) : null;

                return [
                    'product' => $batch->product?->name,
                    'sku' => $batch->product?->sku,
                    'warehouse' => $batch->warehouse?->name,
                    'batch' => $batch->batch_no,
                    'expiry' => $expiry,
                    'days_left' => $expiry ? (int) $today->diffInDays($expiry, false) : null,
                    'quantity' => (float) $batch->quantity,
                    'reserved' => (float) $batch->reserved_quantity,
                    'available' => $available,
                    'state' => match (true) {
                        $expiry !== null && $expiry->lt($today) => 'Expired',
                        $expiry !== null && $expiry->lte($today->copy()->addDays(30)) => 'Expiring soon',
                        $available <= (float) $batch->low_stock_alert => 'Low stock',
                        default => 'OK',
                    },
                ];
            })
            ->all();

        $count = fn (string $state): int => count(array_filter($rows, fn (array $row): bool => $row['state'] === $state));

        return new ReportResult(
            cards: [
                ['label' => 'Batches', 'icon' => 'archive', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Available Quantity', 'icon' => 'package', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'available')), 'type' => 'number'],
                ['label' => 'Expired', 'icon' => 'x-octagon', 'tone' => 'danger', 'value' => $count('Expired'), 'type' => 'number'],
                ['label' => 'Expiring in 30 Days', 'icon' => 'clock', 'tone' => 'warning', 'value' => $count('Expiring soon'), 'type' => 'number'],
                ['label' => 'Low Stock', 'icon' => 'alert-triangle', 'tone' => 'purple', 'value' => $count('Low stock'), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'product', 'label' => 'Product'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'warehouse', 'label' => 'Warehouse'],
                ['key' => 'batch', 'label' => 'Batch'],
                ['key' => 'expiry', 'label' => 'Expiry', 'type' => 'date'],
                ['key' => 'days_left', 'label' => 'Days Left'],
                ['key' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
                ['key' => 'reserved', 'label' => 'Reserved', 'type' => 'number'],
                ['key' => 'available', 'label' => 'Available', 'type' => 'number'],
                ['key' => 'state', 'label' => 'Status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
