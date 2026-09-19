<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\DealerProfile;
use App\Models\Field\DealerVisit;
use App\Services\Admin\Reports\Report;

final class DealerVisitReport extends Report
{
    public function key(): string
    {
        return 'dealer-visits';
    }

    public function title(): string
    {
        return 'Dealer Visits';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'How often each salesman visited each dealer, and when last.';
    }

    public function icon(): string
    {
        return 'map-pin';
    }

    public function filters(): array
    {
        return ['date', 'salesman', 'dealer'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $groups = $this->betweenDates(DealerVisit::query(), 'visited_at', $filters)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->when($filters->dealerId, fn ($query, int $id) => $query->where('dealer_id', $id))
            ->selectRaw('salesman_id, dealer_id, COUNT(*) as visits, MAX(visited_at) as last_visit')
            ->groupBy('salesman_id', 'dealer_id')
            ->orderByDesc('visits')
            ->limit(self::MAX_ROWS)
            ->get();

        $salesmen = $this->userNames($groups->pluck('salesman_id'));
        $firms = DealerProfile::query()->whereIn('user_id', $groups->pluck('dealer_id')->filter()->unique())->pluck('firm_name', 'user_id');
        $dealerNames = $this->userNames($groups->pluck('dealer_id'));

        $rows = $groups->map(fn (DealerVisit $group): array => [
            'salesman' => $salesmen[$group->salesman_id] ?? '#'.$group->salesman_id,
            'dealer' => ($firms[$group->dealer_id] ?? null) ?: ($dealerNames[$group->dealer_id] ?? 'No dealer'),
            'visits' => (int) $group->visits,
            'last_visit' => $group->last_visit,
        ])->all();

        return new ReportResult(
            cards: [
                ['label' => 'Total Visits', 'icon' => 'map-pin', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'visits')), 'type' => 'number'],
                ['label' => 'Salesmen Visiting', 'icon' => 'users', 'tone' => 'info', 'value' => $groups->pluck('salesman_id')->unique()->count(), 'type' => 'number'],
                ['label' => 'Dealers Covered', 'icon' => 'shopping-bag', 'tone' => 'purple', 'value' => $groups->pluck('dealer_id')->filter()->unique()->count(), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'dealer', 'label' => 'Dealer'],
                ['key' => 'visits', 'label' => 'Visits', 'type' => 'number'],
                ['key' => 'last_visit', 'label' => 'Last Visit', 'type' => 'date'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
