<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Contracts\Hr\LeavePolicyContract;
use App\Models\Field\LeaveApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Remaining leave balance per type for the current calendar year.
 *
 * Entitlements come from the admin Leave Policies master (falling back to
 * config/hrms.php), so HR can change policy without a code change, and only
 * approved applications count against the balance.
 */
final class SalesmanLeaveBalanceController extends SalesmanApiController
{
    public function __construct(private readonly LeavePolicyContract $leavePolicies) {}

    public function index(Request $request): JsonResponse
    {
        $salesman = $this->salesman($request);
        $year = (int) $request->integer('year', (int) now()->year);

        $entitlements = $this->leavePolicies->entitlements();

        $taken = LeaveApplication::query()
            ->where('salesman_id', $salesman->id)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->get()
            ->groupBy('leave_type')
            ->map(fn ($group) => $group->sum(
                // Inclusive of both endpoints: a single-day leave is 1 day,
                // not 0.
                fn (LeaveApplication $leave): int => $leave->from_date->diffInDays($leave->to_date) + 1
            ));

        $balances = collect($entitlements)->map(fn ($allowed, string $type): array => [
            'leave_type' => $type,
            'entitled' => (float) $allowed,
            'taken' => (int) ($taken[$type] ?? 0),
            'balance' => max((float) $allowed - (int) ($taken[$type] ?? 0), 0),
        ])->values();

        return $this->success([
            'year' => $year,
            'balances' => $balances,
            'pending_requests' => LeaveApplication::query()
                ->where('salesman_id', $salesman->id)
                ->where('status', 'pending')
                ->count(),
        ]);
    }
}
