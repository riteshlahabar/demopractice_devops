<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Hr\PerformanceReview;
use App\Services\Admin\Reports\Report;

final class PerformanceReport extends Report
{
    public function key(): string
    {
        return 'performance';
    }

    public function title(): string
    {
        return 'Performance';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Performance reviews whose period overlaps the date range: sales, collection and dealer-visit scores with the overall rating.';
    }

    public function icon(): string
    {
        return 'award';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $reviews = PerformanceReview::query()
            ->with('salesman:id,name', 'reviewer:id,name')
            ->whereDate('period_start', '<=', $filters->to)
            ->whereDate('period_end', '>=', $filters->from)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->orderByDesc('period_start')
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $reviews->map(fn (PerformanceReview $review): array => [
            'salesman' => $review->salesman?->name,
            'from' => $review->period_start,
            'to' => $review->period_end,
            'sales' => (float) $review->sales_score,
            'collection' => (float) $review->collection_score,
            'visits' => (float) $review->visit_score,
            'overall' => (float) $review->overall_rating,
            'reviewer' => $review->reviewer?->name,
            'status' => $review->status,
        ])->all();

        // A draft review is still unrated work in progress, so the average is
        // taken over published ones only and says so on the card.
        $published = array_values(array_filter($rows, static fn (array $row): bool => $row['status'] !== 'draft'));
        $ratings = array_filter(array_column($published, 'overall'));

        return new ReportResult(
            cards: [
                ['label' => 'Reviews', 'icon' => 'clipboard', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Published', 'icon' => 'check-circle', 'tone' => 'primary', 'value' => count($published), 'type' => 'number'],
                ['label' => 'Average Rating', 'icon' => 'star', 'tone' => 'purple', 'value' => $ratings === [] ? 0 : round(array_sum($ratings) / count($ratings), 2), 'type' => 'number'],
                ['label' => 'Salesmen Reviewed', 'icon' => 'users', 'tone' => 'warning', 'value' => count(array_unique(array_column($rows, 'salesman'))), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'from', 'label' => 'From', 'type' => 'date'],
                ['key' => 'to', 'label' => 'To', 'type' => 'date'],
                ['key' => 'sales', 'label' => 'Sales Score', 'type' => 'number'],
                ['key' => 'collection', 'label' => 'Collection Score', 'type' => 'number'],
                ['key' => 'visits', 'label' => 'Visit Score', 'type' => 'number'],
                ['key' => 'overall', 'label' => 'Overall Rating', 'type' => 'number'],
                ['key' => 'reviewer', 'label' => 'Reviewed By'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }
}
