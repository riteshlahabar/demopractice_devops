<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\ReturnRequest;
use App\Services\Sales\ReturnRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Return requests raised by a customer or dealer against their own orders.
 */
class ReturnRequestController extends ApiController
{
    public function __construct(private readonly ReturnRequestService $returns) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $requests = ReturnRequest::query()
            ->where('user_id', $user->id)
            ->with('order:id,order_no,grand_total,status')
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        return $this->success([
            'returns' => $requests->items(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:2000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $return = $this->returns->open($user, $validated);
        } catch (RuntimeException $exception) {
            return $this->fail($exception->getMessage(), 422);
        }

        return $this->success(['return' => $return], 'Return request submitted.', 201);
    }

    public function show(Request $request, int $returnRequest): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $model = ReturnRequest::query()
            ->where('user_id', $user->id)
            ->with('order.items.product:id,name,sku')
            ->whereKey($returnRequest)
            ->first();

        if ($model === null) {
            return $this->fail('Return request not found.', 404);
        }

        return $this->success(['return' => $model]);
    }
}
