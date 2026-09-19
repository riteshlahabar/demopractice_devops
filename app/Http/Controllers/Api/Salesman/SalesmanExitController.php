<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Hr\Resignation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Resignation request and full & final status for the salesman's own
 * employment. The notice period and settlement figures are filled and
 * approved by HR — the salesman only requests and reads status, never
 * touches the settlement amount.
 */
final class SalesmanExitController extends SalesmanApiController
{
    public function index(Request $request): JsonResponse
    {
        $resignations = Resignation::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest('resignation_date')
            ->get();

        return $this->success(['resignations' => $resignations]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $hasOpenRequest = Resignation::query()
            ->where('salesman_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasOpenRequest) {
            return $this->fail('A resignation request is already pending or approved.', 422);
        }

        $validated = $request->validate([
            'resignation_date' => ['required', 'date'],
            'requested_last_working_date' => ['nullable', 'date', 'after_or_equal:resignation_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $resignation = Resignation::query()->create($validated + [
            'salesman_id' => $user->id,
            'reference_no' => 'RES-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'notice_period_days' => 30,
            'status' => 'pending',
        ]);

        return $this->success(['resignation' => $resignation], 'Resignation request submitted.', 201);
    }
}
