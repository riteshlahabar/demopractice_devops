<?php

namespace App\Http\Controllers\Api\Dealer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Finance\DealerStatement;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Credit position for the signed-in dealer: limit, outstanding balance and the
 * headroom left to place another order.
 */
class DealerOutstandingController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $profile = $user->dealerProfile;
        $creditLimit = (float) ($profile->credit_limit ?? 0);
        $outstanding = (float) ($profile->outstanding_balance ?? 0);

        return $this->success([
            'credit_limit' => $creditLimit,
            'outstanding_balance' => $outstanding,
            // Never negative: an over-limit dealer has no headroom, not
            // "minus headroom", and the app renders this straight onto a gauge.
            'available_credit' => round(max($creditLimit - $outstanding, 0), 2),
            'is_over_limit' => $outstanding > $creditLimit,
            'unpaid_orders' => Order::query()
                ->where('dealer_id', $user->id)
                ->where('payment_status', '!=', 'paid')
                ->count(),
            'last_payment' => Payment::query()
                ->where('payer_id', $user->id)
                ->latest('paid_at')
                ->first(['id', 'payment_no', 'amount', 'payment_mode', 'paid_at']),
        ]);
    }

    public function ledger(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $entries = DealerStatement::query()
            ->where('dealer_id', $user->id)
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return $this->success([
            'entries' => $entries->items(),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'total' => $entries->total(),
            ],
        ]);
    }
}
