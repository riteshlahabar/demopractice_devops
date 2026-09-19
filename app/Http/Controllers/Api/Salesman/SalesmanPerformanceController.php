<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Field\SalarySlip;
use App\Models\Hr\PerformanceReview;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Performance reviews and the incentive/commission history behind them.
 */
class SalesmanPerformanceController extends ApiController
{
    public function reviews(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $reviews = PerformanceReview::query()
            ->where('salesman_id', $user->id)
            // Drafts are internal to HR until published.
            ->where('status', '!=', 'draft')
            ->with('reviewer:id,name')
            ->latest('period_start')
            ->get();

        return $this->success([
            'reviews' => $reviews,
            'latest' => $reviews->first(),
        ]);
    }

    public function incentives(Request $request): JsonResponse
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
            ->orderBy('salary_month')
            ->get(['id', 'salary_year', 'salary_month', 'incentives', 'commission', 'bonus']);

        return $this->success([
            'year' => $year,
            'months' => $slips,
            'totals' => [
                'incentives' => (float) $slips->sum('incentives'),
                'commission' => (float) $slips->sum('commission'),
                'bonus' => (float) $slips->sum('bonus'),
            ],
        ]);
    }
}
