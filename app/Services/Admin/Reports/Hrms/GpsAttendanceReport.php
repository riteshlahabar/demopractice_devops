<?php

namespace App\Services\Admin\Reports\Hrms;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;
use App\Models\Field\AttendanceLog;
use App\Services\Admin\Reports\Report;

final class GpsAttendanceReport extends Report
{
    public function key(): string
    {
        return 'gps-attendance';
    }

    public function title(): string
    {
        return 'GPS Attendance';
    }

    public function section(): string
    {
        return self::SECTION_HRMS;
    }

    public function description(): string
    {
        return 'Every check in and check out with the coordinates it was marked from, so attendance marked without GPS is visible.';
    }

    public function icon(): string
    {
        return 'map-pin';
    }

    public function filters(): array
    {
        return ['date', 'salesman'];
    }

    public function build(ReportFilters $filters): ReportResult
    {
        $logs = AttendanceLog::query()
            ->with('salesman:id,name')
            ->whereBetween('attendance_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->salesmanId, fn ($query, int $id) => $query->where('salesman_id', $id))
            ->orderByDesc('attendance_date')
            ->limit(self::MAX_ROWS)
            ->get();

        $rows = $logs->map(fn (AttendanceLog $log): array => [
            'date' => $log->attendance_date,
            'salesman' => $log->salesman?->name,
            'check_in' => $log->check_in_at?->format('H:i'),
            'check_in_location' => $this->coordinates($log->check_in_latitude, $log->check_in_longitude),
            'check_out' => $log->check_out_at?->format('H:i'),
            'check_out_location' => $this->coordinates($log->check_out_latitude, $log->check_out_longitude),
            'hours' => round((int) $log->working_minutes / 60, 1),
            'status' => $log->status,
        ])->all();

        $withIn = count(array_filter(array_column($rows, 'check_in_location'), static fn (?string $value): bool => $value !== null));
        $withOut = count(array_filter(array_column($rows, 'check_out_location'), static fn (?string $value): bool => $value !== null));

        return new ReportResult(
            cards: [
                ['label' => 'Attendance Records', 'icon' => 'calendar', 'tone' => 'info', 'value' => count($rows), 'type' => 'number'],
                ['label' => 'GPS Check In', 'icon' => 'map-pin', 'tone' => 'primary', 'value' => $withIn, 'type' => 'number'],
                ['label' => 'GPS Check Out', 'icon' => 'log-out', 'tone' => 'purple', 'value' => $withOut, 'type' => 'number'],
                ['label' => 'Without GPS', 'icon' => 'alert-triangle', 'tone' => 'danger', 'value' => count($rows) - $withIn, 'type' => 'number'],
            ],
            columns: [
                ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'salesman', 'label' => 'Salesman'],
                ['key' => 'check_in', 'label' => 'Check In'],
                ['key' => 'check_in_location', 'label' => 'Check In Location'],
                ['key' => 'check_out', 'label' => 'Check Out'],
                ['key' => 'check_out_location', 'label' => 'Check Out Location'],
                ['key' => 'hours', 'label' => 'Working Hours', 'type' => 'number'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ],
            rows: $rows,
            note: $this->capNote($rows),
        );
    }

    /**
     * Null when either half is missing — a single coordinate locates nothing,
     * and the empty cell is what makes attendance marked without GPS visible.
     */
    private function coordinates(mixed $latitude, mixed $longitude): ?string
    {
        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return null;
        }

        return round((float) $latitude, 5).', '.round((float) $longitude, 5);
    }
}
