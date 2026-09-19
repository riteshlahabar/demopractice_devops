<?php

namespace App\Services\Admin\Reports\Erp;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\DealerProfile;
use App\Services\Admin\Reports\Report;

final class DealerOutstandingReport extends Report
{
    public function key(): string
    {
        return 'dealer-outstanding';
    }

    public function title(): string
    {
        return 'Dealer Outstanding';
    }

    public function section(): string
    {
        return self::SECTION_ERP;
    }

    public function description(): string
    {
        return 'Current credit position of every dealer, highest outstanding first. Not date based.';
    }

    public function icon(): string
    {
        return 'alert-circle';
    }

    public function filters(): array
    {
        return ['salesman', 'dealer'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $rows = DealerProfile::query()
            ->with(['user:id,name,mobile', 'salesman:id,name'])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->when($filters->dealerId, fn ($query, int $id) => $query->where('user_id', $id))
            ->orderByDesc('outstanding_balance')
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(function (DealerProfile $profile): array {
                $limit = (float) $profile->credit_limit;
                $outstanding = (float) $profile->outstanding_balance;

                return [
                    'code' => $profile->dealer_code,
                    'firm' => $profile->firm_name ?: $profile->user?->name,
                    'mobile' => $profile->user?->mobile,
                    'salesman' => $profile->salesman?->name,
                    'credit_limit' => $limit,
                    'outstanding' => $outstanding,
                    'available' => max($limit - $outstanding, 0),
                    'used' => $this->percent($outstanding, $limit),
                ];
            })
            ->all();

        return new ReportResult(
            cards: [
                ['label' => 'Total Outstanding', 'icon' => 'alert-circle', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'outstanding')), 'type' => 'money'],
                ['label' => 'Total Credit Limit', 'icon' => 'credit-card', 'tone' => 'info', 'value' => array_sum(array_column($rows, 'credit_limit')), 'type' => 'money'],
                ['label' => 'Dealers with Balance', 'icon' => 'users', 'tone' => 'purple', 'value' => count(array_filter($rows, fn (array $row): bool => $row['outstanding'] > 0)), 'type' => 'number'],
                ['label' => 'Over Credit Limit', 'icon' => 'alert-triangle', 'tone' => 'danger', 'value' => count(array_filter($rows, fn (array $row): bool => $row['credit_limit'] > 0 && $row['outstanding'] > $row['credit_limit'])), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'code', 'label' => 'Dealer Code'],
                ['key' => 'firm', 'label' => 'Firm'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'money'],
                ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money'],
                ['key' => 'available', 'label' => 'Available Credit', 'type' => 'money'],
                ['key' => 'used', 'label' => 'Limit Used', 'type' => 'percent'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
