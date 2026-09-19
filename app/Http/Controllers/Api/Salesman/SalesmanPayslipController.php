<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Field\SalarySlip;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Payslips for the signed-in salesman.
 *
 * Draft slips are hidden: payroll should not be visible to the employee until
 * HR has finalised the month.
 */
class SalesmanPayslipController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $year = (int) $request->integer('year', (int) now()->year);

        $slips = SalarySlip::query()
            ->where('salesman_id', $user->id)
            ->where('status', '!=', 'draft')
            ->where('salary_year', $year)
            ->orderByDesc('salary_month')
            ->get();

        return $this->success([
            'year' => $year,
            'payslips' => $slips,
            'totals' => [
                'net_paid' => (float) $slips->sum('net_salary'),
                'incentives' => (float) $slips->sum('incentives'),
                'deductions' => (float) $slips->sum('deductions'),
            ],
        ]);
    }

    public function show(Request $request, int $payslip): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $slip = SalarySlip::query()
            ->with('lines')
            ->where('salesman_id', $user->id)
            ->where('status', '!=', 'draft')
            ->whereKey($payslip)
            ->first();

        if ($slip === null) {
            return $this->fail('Payslip not found.', 404);
        }

        return $this->success(['payslip' => $slip]);
    }
}
