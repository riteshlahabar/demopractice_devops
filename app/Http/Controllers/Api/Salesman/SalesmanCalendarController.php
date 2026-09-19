<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Field\AttendanceLog;
use App\Models\Hr\Holiday;
use App\Models\Hr\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Calendar-shaped HR data: the holiday list, the salesman's current shift and
 * their monthly attendance sheet.
 */
class SalesmanCalendarController extends ApiController
{
    public function holidays(Request $request): JsonResponse
    {
        if (($user = $this->requireUser($request, User::ROLE_SALESMAN)) instanceof JsonResponse) {
            return $user;
        }

        $year = (int) $request->integer('year', (int) now()->year);

        return $this->success([
            'year' => $year,
            'holidays' => Holiday::query()
                ->forYear($year)
                ->orderBy('holiday_date')
                ->get(),
        ]);
    }

    public function shift(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $assignment = ShiftAssignment::query()
            ->where('salesman_id', $user->id)
            ->current()
            ->with('shift')
            ->latest('effective_from')
            ->first();

        return $this->success([
            'assignment' => $assignment,
            'shift' => $assignment?->shift,
        ]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        // Accepts `YYYY-MM`; anything unparseable falls back to this month
        // rather than returning the whole history.
        $month = $request->string('month', now()->format('Y-m'))->toString();
        [$year, $monthNumber] = array_pad(array_map('intval', explode('-', $month)), 2, 0);

        if ($year < 2000 || $monthNumber < 1 || $monthNumber > 12) {
            [$year, $monthNumber] = [(int) now()->year, (int) now()->month];
        }

        $logs = AttendanceLog::query()
            ->where('salesman_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $monthNumber)
            ->orderBy('attendance_date')
            ->get();

        return $this->success([
            'month' => sprintf('%04d-%02d', $year, $monthNumber),
            'logs' => $logs,
            'summary' => [
                'present' => $logs->where('status', 'present')->count(),
                'half_day' => $logs->where('status', 'half_day')->count(),
                'absent' => $logs->where('status', 'absent')->count(),
                'working_minutes' => (int) $logs->sum('working_minutes'),
            ],
        ]);
    }
}
