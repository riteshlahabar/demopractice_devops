<?php

namespace App\Services\Admin\People;

use App\Contracts\Admin\People\PersonSummaryBuilderContract;
use App\Data\Admin\People\PersonSummary;
use App\Models\User;
use App\Services\Admin\Reports\ReportValueFormatter;

final class CustomerSummaryBuilder implements PersonSummaryBuilderContract
{
    public function __construct(
        private readonly PersonOrderHistory $orders,
        private readonly ReportValueFormatter $formatter,
    ) {}

    public function role(): string
    {
        return User::ROLE_CUSTOMER;
    }

    public function build(User $person): PersonSummary
    {
        $profile = $person->customerProfile;
        $totals = $this->orders->totals('customer_id', (int) $person->getKey());
        $addresses = $person->addresses()->count();

        return new PersonSummary(
            code: '',
            detailsTitle: 'Customer Details',
            tiles: [
                ['label' => 'Total Orders', 'value' => $this->formatter->format($totals['count'], 'number'), 'icon' => 'shopping-bag', 'tone' => 'primary'],
                ['label' => 'Total Spent', 'value' => $this->formatter->format($totals['value'], 'money'), 'icon' => 'trending-up', 'tone' => 'info'],
                ['label' => 'Last Order', 'value' => $totals['last'] ?? '-', 'icon' => 'calendar', 'tone' => 'purple'],
                ['label' => 'Saved Addresses', 'value' => $this->formatter->format($addresses, 'number'), 'icon' => 'map-pin', 'tone' => 'warning'],
            ],
            details: [
                'Preferred Language' => $this->language($profile?->preferred_language),
                'Date of Birth' => $this->formatter->format($profile?->date_of_birth, 'date'),
                'Saved Addresses' => $this->formatter->format($addresses, 'number'),
                'Last Order' => $totals['last'] ?? '-',
            ],
            tableTitle: 'Recent Orders',
            tableColumns: PersonOrderHistory::TABLE_COLUMNS,
            tableRows: $this->orders->recent('customer_id', (int) $person->getKey()),
            tableEmpty: 'This customer has not placed any orders yet.',
        );
    }

    private function language(?string $code): string
    {
        $names = ['en' => 'English', 'hi' => 'Hindi', 'mr' => 'Marathi', 'gu' => 'Gujarati', 'pa' => 'Punjabi', 'te' => 'Telugu', 'kn' => 'Kannada'];

        return $code === null || $code === '' ? '-' : ($names[$code] ?? strtoupper($code));
    }
}
