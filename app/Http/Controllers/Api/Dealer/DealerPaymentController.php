<?php

namespace App\Http\Controllers\Api\Dealer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Finance\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Payment history for the signed-in dealer.
 */
class DealerPaymentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $payments = Payment::query()
            ->where('payer_id', $user->id)
            ->with('order:id,order_no')
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        return $this->success([
            'payments' => $payments->items(),
            'totals' => [
                'paid' => (float) Payment::query()
                    ->where('payer_id', $user->id)
                    ->where('status', 'completed')
                    ->sum('amount'),
                'pending' => (float) Payment::query()
                    ->where('payer_id', $user->id)
                    ->where('status', 'pending')
                    ->sum('amount'),
            ],
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }
}
