<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Field\LeaveApplication;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\TourPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanHrController extends SalesmanApiController
{
    public function leaves(Request $request): JsonResponse
    {
        $leaves = LeaveApplication::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(20);

        return $this->success(['leaves' => $leaves]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:40'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $leave = LeaveApplication::query()->create($validated + ['salesman_id' => $user->id]);

        return $this->success(['leave' => $leave], 'Leave application submitted.', 201);
    }

    public function assets(Request $request): JsonResponse
    {
        $assets = SalesmanAsset::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->get();

        return $this->success(['assets' => $assets]);
    }

    public function tourPlans(Request $request): JsonResponse
    {
        $plans = TourPlan::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(20);

        return $this->success(['tour_plans' => $plans]);
    }
}
