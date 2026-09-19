<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\DealerProfile;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use App\Services\Admin\Reports\Report;

final class DealerSalesReport extends Report
{
    public function key(): string
    {
        return 'dealer-sales';
    }

    public function title(): string
    {
        return 'Dealer-wise Sales';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Orders, sales and payments received per dealer in the period, with current outstanding.';
    }

    public function icon(): string
    {
        return 'shopping-bag';
    }

    public function filters(): array
    {
        return ['date', 'salesman', 'dealer'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $profiles = DealerProfile::query()
            ->with(['user:id,name', 'salesman:id,name'])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->when($filters->dealerId, fn ($query, int $id) => $query->where('user_id', $id))
            ->limit(self::MAX_ROWS)
            ->get();

        $dealerIds = $profiles->pluck('user_id');

        $sales = $this->betweenDates(Order::query(), 'created_at', $filters)
            ->where('order_type', 'dealer')->where('status', '!=', 'cancelled')->whereIn('dealer_id', $dealerIds)
            ->selectRaw('dealer_id, COUNT(*) as orders, SUM(grand_total) as sales')
            ->groupBy('dealer_id')->get()->keyBy('dealer_id');

        $payments = $this->betweenDates(Payment::query(), 'created_at', $filters)
            ->whereIn('status', self::RECEIVED_PAYMENT_STATUSES)->whereIn('payer_id', $dealerIds)
            ->selectRaw('payer_id, SUM(amount) as received')
            ->groupBy('payer_id')->pluck('received', 'payer_id');

        $rows = $profiles->map(fn (DealerProfile $profile): array => [
            'code' => $profile->dealer_code,
            'firm' => $profile->firm_name ?: $profile->user?->name,
            'salesman' => $profile->salesman?->name,
            'orders' => (int) ($sales[$profile->user_id]->orders ?? 0),
            'sales' => (float) ($sales[$profile->user_id]->sales ?? 0),
            'received' => (float) ($payments[$profile->user_id] ?? 0),
            'outstanding' => (float) $profile->outstanding_balance,
        ])->sortByDesc('sales')->values()->all();

        return new ReportResult(
            cards: [
                ['label' => 'Dealers', 'icon' => 'shopping-bag', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Dealer Sales', 'icon' => 'trending-up', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'sales')), 'type' => 'money'],
                ['label' => 'Payments Received', 'icon' => 'credit-card', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'received')), 'type' => 'money'],
                ['label' => 'Total Outstanding', 'icon' => 'alert-circle', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'outstanding')), 'type' => 'money'],
            ],
            columns: [
                ['key' => 'code', 'label' => 'Dealer Code'],
                ['key' => 'firm', 'label' => 'Firm'],
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'orders', 'label' => 'Orders', 'type' => 'number'],
                ['key' => 'sales', 'label' => 'Sales', 'type' => 'money'],
                ['key' => 'received', 'label' => 'Received', 'type' => 'money'],
                ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
