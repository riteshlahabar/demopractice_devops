<?php

namespace App\Services\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleQueryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ModuleQuery implements ModuleQueryContract
{
    public function base(array $module): Builder
    {
        $model = $module['model'];
        $query = $model::query();

        if (! empty($module['with'])) {
            $query->with($module['with']);
        }

        if (! empty($module['with_count'])) {
            $query->withCount($module['with_count']);
        }

        foreach ($module['where'] ?? [] as $column => $value) {
            $query->where($column, $value);
        }

        foreach ($module['where_not_null'] ?? [] as $column) {
            $query->whereNotNull($column);
        }

        return $query;
    }

    public function filtered(Request $request, array $module): Builder
    {
        $query = $this->base($module);

        $this->applySearch($query, $request, $module);
        $this->applyStatus($query, $request, $module);
        $this->applySubmenu($query, $request, $module);
        $this->applyChannel($query, $request, $module);
        $this->applyConfiguredFilters($query, $request, $module);
        $this->applyDateRange($query, $request, $module);

        return $query;
    }

    /**
     * Searches every column the module lists in `search`.
     */
    private function applySearch(Builder $query, Request $request, array $module): void
    {
        if (! $request->filled('search') || empty($module['search'])) {
            return;
        }

        $term = trim((string) $request->input('search'));

        $query->where(function (Builder $builder) use ($module, $term): void {
            foreach ($module['search'] as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, 'like', '%'.$term.'%');
            }
        });
    }

    private function applyStatus(Builder $query, Request $request, array $module): void
    {
        if ($request->filled('status') && ($module['status_column'] ?? null)) {
            $query->where($module['status_column'], $request->input('status'));
        }
    }

    /**
     * Storefront modules are reached through submenu links that pin the row
     * they belong to.
     */
    private function applySubmenu(Builder $query, Request $request, array $module): void
    {
        $key = $module['key'] ?? '';

        if ($request->filled('placement') && $key === 'storefront-banners') {
            $query->where('placement', $request->query('placement'));
        }

        if ($request->filled('section_key') && $key === 'storefront-sections') {
            $query->where('section_key', $request->query('section_key'));
        }

        if ($request->filled('section_key') && $key === 'storefront-section-products') {
            $query->whereHas('section', fn (Builder $builder) => $builder->where('section_key', $request->query('section_key')));
        }
    }

    /**
     * Sales pages are split into Customer and Dealer (?type=), each with its
     * own permission, so each lists only its own channel's records.
     */
    private function applyChannel(Builder $query, Request $request, array $module): void
    {
        $channel = $request->query('type');
        if (empty($module['channel']) || ! in_array($channel, ['customer', 'dealer'], true)) {
            return;
        }

        $column = $module['channel']['column'];
        $relation = $module['channel']['relation'] ?? null;

        $relation
            ? $query->whereHas($relation, fn (Builder $builder) => $builder->where($column, $channel))
            : $query->where($column, $channel);
    }

    private function applyConfiguredFilters(Builder $query, Request $request, array $module): void
    {
        foreach ($module['filters'] ?? [] as $filter) {
            if (! $request->filled($filter['name'])) {
                continue;
            }

            $column = $filter['column'] ?? $filter['name'];
            $value = $request->input($filter['name']);

            if (! empty($filter['relation'])) {
                $query->whereHas($filter['relation'], fn (Builder $builder) => $builder->where($column, $value));
            } else {
                $query->where($column, $value);
            }
        }
    }

    private function applyDateRange(Builder $query, Request $request, array $module): void
    {
        $dateColumn = $module['date_column'] ?? 'created_at';

        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date('date_from')->toDateString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date('date_to')->toDateString());
        }
    }
}
