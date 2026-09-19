<?php

namespace App\Services\Admin\People;

use App\Contracts\Admin\People\PersonSummaryBuilderContract;
use App\Data\Admin\People\PersonSummary;
use App\Models\DealerProfile;
use App\Models\User;
use App\Services\Admin\Reports\ReportValueFormatter;
use Illuminate\Database\Eloquent\Builder;

final class SalesmanSummaryBuilder implements PersonSummaryBuilderContract
{
    public function __construct(
        private readonly PersonOrderHistory $orders,
        private readonly ReportValueFormatter $formatter,
    ) {}

    public function role(): string
    {
        return User::ROLE_SALESMAN;
    }

    public function build(User $person): PersonSummary
    {
        $profile = $person->salesmanProfile;
        $totals = $this->orders->totals('salesman_id', (int) $person->getKey());
        $dealers = DealerProfile::query()->where('salesman_id', $person->getKey());

        return new PersonSummary(
            code: (string) ($profile?->employee_code ?? ''),
            detailsTitle: 'Employee Details',
            tiles: [
                ['label' => 'Assigned Dealers', 'value' => $this->formatter->format((clone $dealers)->count(), 'number'), 'icon' => 'users', 'tone' => 'primary'],
                ['label' => 'Orders Taken', 'value' => $this->formatter->format($totals['count'], 'number'), 'icon' => 'shopping-bag', 'tone' => 'info'],
                ['label' => 'Sales Value', 'value' => $this->formatter->format($totals['value'], 'money'), 'icon' => 'trending-up', 'tone' => 'purple'],
                ['label' => 'Monthly Target', 'value' => $this->formatter->format($profile?->target_amount ?? 0, 'money'), 'icon' => 'target', 'tone' => 'warning'],
            ],
            details: [
                'Employee Code' => $this->formatter->format($profile?->employee_code),
                'Joining Date' => $this->formatter->format($profile?->joining_date, 'date'),
                'Territory' => $this->formatter->format($profile?->territory),
                'Basic Salary' => $this->formatter->format($profile?->basic_salary ?? 0, 'money'),
                'Monthly Target' => $this->formatter->format($profile?->target_amount ?? 0, 'money'),
            ],
            tableTitle: 'Assigned Dealers',
            tableColumns: [
                ['key' => 'firm', 'label' => 'Firm'],
                ['key' => 'code', 'label' => 'Dealer Code'],
                ['key' => 'contact', 'label' => 'Contact Person'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'status', 'label' => 'Status', 'status' => true],
                ['key' => 'outstanding', 'label' => 'Outstanding', 'align' => 'end'],
            ],
            tableRows: $this->dealerRows($dealers),
            tableEmpty: 'No dealers are assigned to this salesman yet.',
        );
    }

    /**
     * @param  Builder<DealerProfile>  $dealers
     * @return array<int, array<string, string|null>>
     */
    private function dealerRows(Builder $dealers): array
    {
        return $dealers->with('user:id,name,mobile,status')
            ->orderBy('firm_name')
            ->limit(PersonOrderHistory::RECENT_LIMIT)
            ->get()
            ->map(fn (DealerProfile $dealer): array => [
                'url' => $dealer->user ? route('admin.dealers.show', $dealer->user_id) : null,
                'firm' => $this->formatter->format($dealer->firm_name),
                'code' => $this->formatter->format($dealer->dealer_code),
                'contact' => $this->formatter->format($dealer->user?->name),
                'mobile' => $this->formatter->format($dealer->user?->mobile),
                'status' => $this->formatter->format($dealer->user?->status, 'status'),
                'outstanding' => $this->formatter->format($dealer->outstanding_balance, 'money'),
            ])
            ->all();
    }
}
