<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\AttendanceLog;
use App\Services\Admin\Reports\Report;

final class AttendanceReport extends Report
{
    public function key(): string
    {
        return 'attendance';
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Present, late, half day, absent and leave days with working hours per salesman.';
    }

    public function icon(): string
    {
        return 'check-square';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $groups = AttendanceLog::query()
            ->whereBetween('attendance_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->selectRaw("salesman_id, COUNT(*) as days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as on_leave,
                SUM(COALESCE(working_minutes, 0)) as minutes")
            ->groupBy('salesman_id')
            ->get();

        $names = $this->userNames($groups->pluck('salesman_id'));

        $rows = $groups->map(function (AttendanceLog $group) use ($names): array {
            $worked = (int) $group->present + (int) $group->late + (int) $group->half_day;

            return [
                'salesman' => $names[$group->salesman_id] ?? '#'.$group->salesman_id,
                'days' => (int) $group->days,
                'present' => (int) $group->present,
                'late' => (int) $group->late,
                'half_day' => (int) $group->half_day,
                'absent' => (int) $group->absent,
                'leave' => (int) $group->on_leave,
                'hours' => round((int) $group->minutes / 60, 1),
                'attendance' => $this->percent($worked, (int) $group->days),
            ];
        })->sortBy('salesman')->values()->all();

        return new ReportResult(
            cards: [
                ['label' => 'Salesmen', 'icon' => 'users', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'Present Days', 'icon' => 'check-circle', 'tone' => 'primary', 'value' => array_sum(array_column($rows, 'present')) + array_sum(array_column($rows, 'late')), 'type' => 'number'],
                ['label' => 'Absent Days', 'icon' => 'user-x', 'tone' => 'danger', 'value' => array_sum(array_column($rows, 'absent')), 'type' => 'number'],
                ['label' => 'Working Hours', 'icon' => 'clock', 'tone' => 'purple', 'value' => array_sum(array_column($rows, 'hours')), 'type' => 'number'],
            ],
            columns: [
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'days', 'label' => 'Days Marked', 'type' => 'number'],
                ['key' => 'present', 'label' => 'Present', 'type' => 'number'],
                ['key' => 'late', 'label' => 'Late', 'type' => 'number'],
                ['key' => 'half_day', 'label' => 'Half Day', 'type' => 'number'],
                ['key' => 'absent', 'label' => 'Absent', 'type' => 'number'],
                ['key' => 'leave', 'label' => 'Leave', 'type' => 'number'],
                ['key' => 'hours', 'label' => 'Working Hours', 'type' => 'number'],
                ['key' => 'attendance', 'label' => 'Attendance', 'type' => 'percent'],
            ],
            rows: $rows,
        );
    }
}
