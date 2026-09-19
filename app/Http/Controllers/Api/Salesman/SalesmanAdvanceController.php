<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Hr\SalaryAdvance;
use App\Models\User;
use App\Services\Hr\SalaryAdvanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Salary advances and employee loans, with their EMI recovery schedule.
 */
class SalesmanAdvanceController extends ApiController
{
    public function __construct(private readonly SalaryAdvanceService $advances) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $records = SalaryAdvance::query()
            ->where('salesman_id', $user->id)
            ->where('advance_type', $request->string('type', SalaryAdvance::TYPE_ADVANCE))
            ->with('schedule')
            ->latest()
            ->get();

        return $this->success([
            'records' => $records,
            'outstanding_total' => round(
                $records->sum(fn (SalaryAdvance $row): float => $row->outstanding_amount),
                2
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'advance_type' => ['required', 'in:advance,loan'],
            'amount' => ['required', 'numeric', 'min:1'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:60'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $advance = $this->advances->request($user, $validated);
        } catch (RuntimeException $exception) {
            return $this->fail($exception->getMessage(), 422);
        }

        return $this->success(
            ['record' => $advance->load('schedule')],
            'Request submitted for approval.',
            201
        );
    }
}
