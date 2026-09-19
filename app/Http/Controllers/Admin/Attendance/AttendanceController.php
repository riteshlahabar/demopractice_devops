<?php

namespace App\Http\Controllers\Admin\Attendance;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Field\AttendanceLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends AdminModuleController
{
    protected string $moduleKey = 'attendance';

    public function bulk(Request $request): View
    {
        $date = $request->date('attendance_date')?->toDateString() ?? now()->toDateString();
        $salesmen = User::query()
            ->where('role', User::ROLE_SALESMAN)
            ->orderBy('name')
            ->get();

        $existing = AttendanceLog::query()
            ->whereDate('attendance_date', $date)
            ->whereIn('salesman_id', $salesmen->pluck('id'))
            ->get()
            ->keyBy('salesman_id');

        return view('admin.attendance.bulk', [
            'pageTitle' => 'Bulk Attendance',
            'breadcrumbs' => ['Admin', 'Timesheet', 'Bulk Attendance'],
            'date' => $date,
            'salesmen' => $salesmen,
            'existing' => $existing,
            'statuses' => ['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'late' => 'Late', 'leave' => 'Leave'],
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'rows' => ['required', 'array'],
            'rows.*.salesman_id' => ['required', 'integer', 'exists:users,id'],
            'rows.*.mark' => ['nullable', 'boolean'],
            'rows.*.status' => ['required', 'string', 'in:present,absent,half_day,late,leave'],
            'rows.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'rows.*.check_out_time' => ['nullable', 'date_format:H:i'],
            'rows.*.working_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ]);

        $date = Carbon::parse($validated['attendance_date'])->toDateString();
        $saved = 0;

        foreach ($validated['rows'] as $row) {
            if (empty($row['mark'])) {
                continue;
            }

            $checkIn = $this->combineDateAndTime($date, $row['check_in_time'] ?? null);
            $checkOut = $this->combineDateAndTime($date, $row['check_out_time'] ?? null);
            $workingMinutes = (int) ($row['working_minutes'] ?? 0);

            if ($workingMinutes === 0 && $checkIn && $checkOut && $checkOut->greaterThanOrEqualTo($checkIn)) {
                $workingMinutes = $checkIn->diffInMinutes($checkOut);
            }

            AttendanceLog::query()->updateOrCreate(
                ['salesman_id' => $row['salesman_id'], 'attendance_date' => $date],
                [
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,
                    'working_minutes' => $workingMinutes,
                    'status' => $row['status'],
                ]
            );

            $saved++;
        }

        return back()->with('success', $saved.' attendance records saved successfully.');
    }

    private function combineDateAndTime(string $date, ?string $time): ?Carbon
    {
        if (! $time) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i', $date.' '.$time);
    }
}
