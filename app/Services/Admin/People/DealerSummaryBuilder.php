<?php

namespace App\Services\Admin\People;

use App\Contracts\Admin\People\PersonSummaryBuilderContract;
use App\Data\Admin\People\PersonSummary;
use App\Models\User;
use App\Services\Admin\Reports\ReportValueFormatter;

final class DealerSummaryBuilder implements PersonSummaryBuilderContract
{
    public function __construct(
        private readonly PersonOrderHistory $orders,
        private readonly ReportValueFormatter $formatter,
    ) {}

    public function role(): string
    {
        return User::ROLE_DEALER;
    }

    public function build(User $person): PersonSummary
    {
        $profile = $person->dealerProfile;
        $totals = $this->orders->totals('dealer_id', (int) $person->getKey());
        $credit = (float) ($profile?->credit_limit ?? 0);
        $outstanding = (float) ($profile?->outstanding_balance ?? 0);
        $money = fn (float $value): string => $this->formatter->format($value, 'money');

        return new PersonSummary(
            code: (string) ($profile?->dealer_code ?? ''),
            detailsTitle: 'Firm Details',
            tiles: [
                ['label' => 'Total Orders', 'value' => $this->formatter->format($totals['count'], 'number'), 'icon' => 'shopping-bag', 'tone' => 'primary'],
                ['label' => 'Total Purchase', 'value' => $money($totals['value']), 'icon' => 'trending-up', 'tone' => 'info'],
                ['label' => 'Outstanding', 'value' => $money($outstanding), 'icon' => 'alert-circle', 'tone' => 'warning'],
                ['label' => 'Credit Limit', 'value' => $money($credit), 'icon' => 'credit-card', 'tone' => 'purple'],
            ],
            details: [
                'Firm Name' => $this->text($profile?->firm_name),
                'Dealer Code' => $this->text($profile?->dealer_code),
                'GST Number' => $this->text($profile?->gst_number),
                'Assigned Salesman' => $this->text($profile?->salesman?->name),
                'Available Credit' => $money(max(0, $credit - $outstanding)),
                'Last Order' => $totals['last'] ?? '-',
                'Approved On' => $this->formatter->format($profile?->approved_at, 'date'),
            ],
            tableTitle: 'Recent Orders',
            tableColumns: PersonOrderHistory::TABLE_COLUMNS,
            tableRows: $this->orders->recent('dealer_id', (int) $person->getKey()),
            tableEmpty: 'This dealer has not placed any orders yet.',
        );
    }

    private function text(mixed $value): string
    {
        return $this->formatter->format($value);
    }
}
