<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Hr\SalaryRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only history of the salesman's own basic-salary changes.
 */
final class SalesmanSalaryRevisionController extends SalesmanApiController
{
    public function index(Request $request): JsonResponse
    {
        $revisions = SalaryRevision::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->orderByDesc('effective_from')
            ->get();

        return $this->success([
            'revisions' => $revisions->map(fn (SalaryRevision $revision): array => [
                'id' => $revision->id,
                'previous_basic' => (float) $revision->previous_basic,
                'new_basic' => (float) $revision->new_basic,
                'change_amount' => $revision->change_amount,
                'change_percent' => $revision->change_percent,
                'effective_from' => $revision->effective_from?->toDateString(),
                'reason' => $revision->reason,
                'reason_label' => $revision->reason_label,
                'notes' => $revision->notes,
                'applied' => $revision->applied_at !== null,
            ])->all(),
        ]);
    }
}
