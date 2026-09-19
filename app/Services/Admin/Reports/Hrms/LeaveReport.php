<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\LeaveApplication;
use App\Services\Admin\Reports\Report;
use Illuminate\Support\Carbon;

final class LeaveReport extends Report
{
    public function key(): string
    {
        return 'leave';
    }

    public function title(): string
    {
        return 'Leave';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Leave applications overlapping the period, by salesman and leave type.';
    }

    public function icon(): string
    {
        return 'calendar';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $applications = LeaveApplication::query()
            ->whereDate('from_date', '<=', $filters->to)
            ->whereDate('to_date', '>=', $filters->from)
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->get(['salesman_id', 'leave_type', 'from_date', 'to_date', 'status']);

        $names = $this->userNames($applications->pluck('salesman_id'));
        $days = fn (LeaveApplication $leave): int => (int) Carbon::parse($leave->from_date)->diffInDays(Carbon::parse($leave->to_date)) + 1;

        $rows = $applications
            ->groupBy(fn (LeaveApplication $leave): string => $leave->salesman_id.'|'.$leave->leave_type)
            ->map(fn ($group): array => [
                'salesman' => $names[$group->first()->salesman_id] ?? '#'.$group->first()->salesman_id,
                'type' => $group->first()->leave_type,
                'applications' => $group->count(),
                'approved_days' => $group->where('status', 'approved')->sum($days),
                'pending' => $group->where('status', 'pending')->count(),
                'rejected' => $group->where('status', 'rejected')->count(),
            ])
            ->sortBy('salesman')->values()->all();

        return new ReportResult(
            cards: [
                ['label' => 'Applications', 'icon' => 'file-text', 'tone' => 'info', 'value' => $applications->count(), 'type' => 'number'],
                ['label' => 'Approved Leave Days', 'icon' => 'calendar', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'approved_days')), 'type' => 'number'],
                ['label' => 'Pending', 'icon' => 'clock', 'tone' => 'warning', 'value' => array_sum(array_column($rows, 'pending')), 'type' => 'number'],
                ['label' => 'Rejected', 'icon' => 'x-circle', 'tone' => 'danger', 'value' => array_sum(array_column($rows, 'rejected')), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'type', 'label' => 'Leave Type', 'type' => 'status'],
                ['key' => 'applications', 'label' => 'Applications', 'type' => 'number'],
                ['key' => 'approved_days', 'label' => 'Approved Days', 'type' => 'number'],
                ['key' => 'pending', 'label' => 'Pending', 'type' => 'number'],
                ['key' => 'rejected', 'label' => 'Rejected', 'type' => 'number'],
            ],
            rows: $rows,
        );
    }
}
