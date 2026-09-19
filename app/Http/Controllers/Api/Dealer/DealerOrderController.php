<?php

namespace App\Http\Controllers\Api\Dealer;

use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\Sales\Orders\OrderItemImageAttacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealerOrderController extends ApiController
{
    public function index(Request $request, OrderItemImageAttacher $images): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $orders = Order::query()
            ->with('items.product.images', 'items.variant', 'invoice', 'dispatches', 'salesman')
            ->where('dealer_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        $images->attach($orders->getCollection());

        return $this->success(['orders' => $orders]);
    }

    public function store(Request $request, OrderWorkflowContract $orders): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = $orders->createForDealer($user, $validated['items'], $validated['notes'] ?? null);

        return $this->success(['order' => $order], 'Dealer order sent to assigned salesman.', 201);
    }

    public function show(Request $request, Order $order, OrderItemImageAttacher $images): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        if ((int) $order->dealer_id !== (int) $user->id) {
            return $this->fail('Order not found.', 404);
        }

        $order->load('items.product.images', 'items.variant', 'invoice', 'dispatches', 'salesman');
        $images->attach([$order]);

        return $this->success(['order' => $order]);
    }
}
