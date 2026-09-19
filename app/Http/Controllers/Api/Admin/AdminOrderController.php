<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\Sales\OrderStatusContract;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminOrderController extends AdminApiController
{
    /**
     * Cancelling is the only status an admin sets directly; the rest follow the
     * invoice and dispatch steps.
     */
    public function cancel(Request $request, OrderStatusContract $status, Order $order): JsonResponse
    {
        $admin = $this->admin($request);

        $reason = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ])['cancel_reason'];

        if (! $status->cancel($order, $reason, $admin->id)) {
            return $this->fail('A delivered or already cancelled order cannot be cancelled.');
        }

        return $this->success(['order' => $order->fresh('items.product')], 'Order cancelled.');
    }

    public function upsertDispatch(Request $request, Order $order): JsonResponse
    {
        $this->admin($request);

        $validated = $request->validate([
            'dispatch_no' => ['required', 'string', 'max:80'],
            'status' => ['required', 'string', 'max:40'],
            'courier_name' => ['nullable', 'string', 'max:255'],
            'tracking_no' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'string', 'max:255'],
            'current_latitude' => ['nullable', 'numeric'],
            'current_longitude' => ['nullable', 'numeric'],
        ]);

        $dispatch = Dispatch::query()->updateOrCreate(
            ['order_id' => $order->id, 'dispatch_no' => $validated['dispatch_no']],
            $validated
        );

        return $this->success(['dispatch' => $dispatch], 'Dispatch updated.');
    }
}
