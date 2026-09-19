<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanAttendanceController extends SalesmanApiController
{
    public function checkIn(Request $request): JsonResponse
    {
        $user = $this->salesman($request);
        $validated = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);

        $attendance = AttendanceLog::query()->updateOrCreate(
            ['salesman_id' => $user->id, 'attendance_date' => today()],
            [
                'check_in_at' => now(),
                'check_in_latitude' => $validated['latitude'],
                'check_in_longitude' => $validated['longitude'],
                'status' => 'present',
            ]
        );

        return $this->success(['attendance' => $attendance], 'Checked in.');
    }

    public function checkOut(Request $request): JsonResponse
    {
        $user = $this->salesman($request);
        $validated = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);

        $attendance = AttendanceLog::query()
            ->where('salesman_id', $user->id)
            ->where('attendance_date', today())
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return $this->fail('Check in is required before check out.');
        }

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'working_minutes' => Carbon::parse($attendance->check_in_at)->diffInMinutes(now()),
        ]);

        return $this->success(['attendance' => $attendance], 'Checked out.');
    }

    public function visits(Request $request): JsonResponse
    {
        $visits = DealerVisit::query()
            ->with('dealer.dealerProfile')
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(20);

        return $this->success(['visits' => $visits]);
    }

    public function storeVisit(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'dealer_id' => ['required', 'integer', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $dealer = User::query()->findOrFail($validated['dealer_id']);

        if ((int) $dealer->dealerProfile?->salesman_id !== (int) $user->id) {
            return $this->fail('Dealer is not assigned to this salesman.', 403);
        }

        $visit = DealerVisit::query()->create($validated + ['salesman_id' => $user->id, 'visited_at' => now()]);

        return $this->success(['visit' => $visit], 'Dealer visit saved.', 201);
    }
}
