<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Api\ApiController;
use App\Services\Finance\OnlinePaymentService;
use App\Services\Sales\Access\OrderOwnershipScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Opens an online payment for an order and hands the app a URL to open.
 *
 * The app never builds the gateway URL itself: the merchant key would have to
 * ship inside the APK for that, where anyone can read it.
 */
final class OnlinePaymentController extends ApiController
{
    public function __construct(
        private readonly OnlinePaymentService $payments,
        private readonly OrderOwnershipScope $orders,
    ) {}

    public function start(Request $request, int $order): JsonResponse
    {
        $user = $this->requireUser($request);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        // Ownership is resolved through the shared scope, so this endpoint
        // cannot pay for somebody else's order by guessing an id.
        $found = $this->orders->find($user, $order);

        if (! $found) {
            return $this->fail('Order not found.', 404);
        }

        return $this->success($this->payments->start($found, $user), 'Payment started.');
    }
}
