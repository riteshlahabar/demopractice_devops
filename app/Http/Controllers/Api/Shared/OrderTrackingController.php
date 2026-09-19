<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use App\Services\Sales\Access\OrderOwnershipScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Order timeline for the "track my order" screen in all three apps.
 */
class OrderTrackingController extends ApiController
{
    public function __construct(private readonly OrderOwnershipScope $scope) {}

    public function show(Request $request, int $order): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $model = $this->scope->find($user, $order, ['dispatches', 'invoice']);

        if ($model === null) {
            return $this->fail('Order not found.', 404);
        }

        $dispatch = $model->dispatches->sortByDesc('created_at')->first();

        return $this->success([
            'order' => $model->only(['id', 'order_no', 'status', 'grand_total', 'created_at']),
            'stages' => $this->stages($model, $dispatch),
            'courier' => $this->courier($dispatch),
            'invoice' => $model->invoice,
        ]);
    }

    /**
     * The ordered checkpoint list the UI draws as a vertical stepper. Each
     * stage carries the moment it happened, or null while it is still ahead of
     * the order, so the app needs no status-to-step mapping of its own.
     */
    private function stages(Order $order, ?Dispatch $dispatch): array
    {
        return [
            $this->stage('placed', 'Order placed', $order->created_at),
            $this->stage('approved', 'Approved', $order->approved_at),
            $this->stage('packed', 'Packed', $dispatch?->created_at),
            $this->stage('dispatched', 'Dispatched', $dispatch?->dispatched_at),
            $this->stage('out_for_delivery', 'Out for delivery', $this->outForDeliveryAt($order, $dispatch)),
            $this->stage('delivered', 'Delivered', $dispatch?->delivered_at),
        ];
    }

    /**
     * The recorded time first; otherwise inferred from the status, or from the
     * delivery itself, so a delivered order never shows a skipped step.
     */
    private function outForDeliveryAt(Order $order, ?Dispatch $dispatch): ?object
    {
        if ($dispatch?->out_for_delivery_at) {
            return $dispatch->out_for_delivery_at;
        }

        if ($order->status === 'out_for_delivery' || $dispatch?->status === 'out_for_delivery') {
            return $dispatch?->updated_at ?? $order->updated_at;
        }

        return $dispatch?->delivered_at;
    }

    private function stage(string $key, string $label, ?object $at): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'at' => $at?->toIso8601String(),
            'done' => $at !== null,
        ];
    }

    private function courier(?Dispatch $dispatch): ?array
    {
        if ($dispatch === null) {
            return null;
        }

        return [
            'name' => $dispatch->courier_name,
            'tracking_no' => $dispatch->tracking_no,
            'tracking_url' => $dispatch->tracking_url,
            'status' => $dispatch->status,
        ];
    }
}
