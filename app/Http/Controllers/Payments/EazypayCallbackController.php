<?php

namespace App\Http\Controllers\Payments;

use App\Contracts\Finance\PaymentGatewayContract;
use App\Http\Controllers\Controller;
use App\Services\Finance\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Where the bank sends the payer back.
 *
 * This route is public and unauthenticated because the payer arrives from the
 * bank's domain with no session guarantees — which is exactly why nothing here
 * is believed without the signature. The response decides what we display; it
 * never decides, on its own, that money arrived.
 */
final class EazypayCallbackController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayContract $gateway,
        private readonly OnlinePaymentService $payments,
    ) {}

    public function handle(Request $request): RedirectResponse
    {
        $result = $this->gateway->parse($request->all());

        $payment = $this->payments->settle($result);

        if (! $payment) {
            return $this->back('We could not verify that payment. If money left your account, contact support before paying again.');
        }

        if ($payment->status === 'success') {
            return redirect()
                ->route('store.page', ['page' => 'orders'])
                ->with('success', "Payment received. Reference {$payment->payment_no}.");
        }

        return $this->back($payment->failure_reason ?: 'The payment did not go through. Nothing has been charged for this attempt.');
    }

    private function back(string $message): RedirectResponse
    {
        return redirect()->route('store.page', ['page' => 'orders'])->with('error', $message);
    }
}
