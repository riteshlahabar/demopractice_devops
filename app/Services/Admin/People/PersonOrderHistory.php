<?php

namespace App\Services\Admin\People;

use App\Models\Sales\Order;
use App\Services\Admin\Reports\ReportValueFormatter;

/**
 * Order figures shared by the dealer, customer and salesman summaries.
 * `$column` is the orders column that links the person (dealer_id, ...).
 */
final class PersonOrderHistory
{
    public const RECENT_LIMIT = 10;

    public const TABLE_COLUMNS = [
        ['key' => 'order_no', 'label' => 'Order No.'],
        ['key' => 'date', 'label' => 'Date'],
        ['key' => 'channel', 'label' => 'Channel'],
        ['key' => 'status', 'label' => 'Status', 'status' => true],
        ['key' => 'total', 'label' => 'Total', 'align' => 'end'],
    ];

    public function __construct(private readonly ReportValueFormatter $formatter) {}

    /**
     * @return array{count: int, value: float, last: ?string}
     */
    public function totals(string $column, int $userId): array
    {
        $orders = Order::query()->where($column, $userId);
        $last = (clone $orders)->latest()->value('created_at');

        return [
            'count' => (clone $orders)->count(),
            'value' => (float) (clone $orders)->where('status', '!=', 'cancelled')->sum('grand_total'),
            'last' => $last ? $this->formatter->format($last, 'date') : null,
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function recent(string $column, int $userId): array
    {
        return Order::query()
            ->where($column, $userId)
            ->latest()
            ->limit(self::RECENT_LIMIT)
            ->get(['id', 'order_no', 'order_type', 'status', 'grand_total', 'created_at'])
            ->map(fn (Order $order): array => [
                'url' => route('admin.orders.show', $order->getKey()),
                'order_no' => (string) $order->order_no,
                'date' => $this->formatter->format($order->created_at, 'date'),
                'channel' => $this->formatter->format($order->order_type, 'status'),
                'status' => $this->formatter->format($order->status, 'status'),
                'total' => $this->formatter->format($order->grand_total, 'money'),
            ])
            ->all();
    }
}
