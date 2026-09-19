<?php

namespace App\Services\Admin\Reports;

use App\Contracts\Admin\Reports\ReportContract;
use App\Data\Admin\Reports\ReportFilters;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Small shared helpers for reports; each subclass still owns its own query.
 */
abstract class Report implements ReportContract
{
    /** Payment statuses that mean money was actually received. */
    protected const RECEIVED_PAYMENT_STATUSES = ['paid', 'collected', 'verified'];

    /** Rows shown or exported per report, so a wide range cannot exhaust memory. */
    protected const MAX_ROWS = 2000;

    public function description(): string
    {
        return '';
    }

    public function icon(): string
    {
        return 'bar-chart-2';
    }

    protected function betweenDates(Builder $query, string $column, ReportFilters $filters): Builder
    {
        return $query->whereBetween($column, [$filters->from, $filters->to]);
    }

    protected function percent(float $part, float $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : 0.0;
    }

    /**
     * Display names for a set of user ids, in one query.
     *
     * @param  iterable<int|null>  $ids
     * @return Collection<int, string>
     */
    protected function userNames(iterable $ids): Collection
    {
        $ids = collect($ids)->filter()->unique()->values();

        return $ids->isEmpty() ? collect() : User::query()->whereKey($ids)->pluck('name', 'id');
    }

    /**
     * @param  array<int, mixed>  $rows
     */
    protected function capNote(array $rows): ?string
    {
        return count($rows) >= self::MAX_ROWS
            ? 'Showing the first '.self::MAX_ROWS.' rows. Narrow the filters to see the rest.'
            : null;
    }
}
