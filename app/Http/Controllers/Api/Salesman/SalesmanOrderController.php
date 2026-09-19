<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanOrderController extends SalesmanApiController
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with('dealer.dealerProfile', 'items.product', 'items.variant', 'dispatches')
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success(['orders' => $orders]);
    }

    public function store(Request $request, OrderWorkflowContract $orders): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'dealer_id' => ['required', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);

        $dealer = User::query()->where('role', User::ROLE_DEALER)->findOrFail($validated['dealer_id']);
        $order = $orders->createBySalesman($user, $dealer, $validated['items'], $validated['notes'] ?? null);

        return $this->success(['order' => $order], 'Dealer order created.', 201);
    }

    public function forwardToAdmin(Request $request, Order $order): JsonResponse
    {
        $user = $this->salesman($request);

        if ((int) $order->salesman_id !== (int) $user->id) {
            return $this->fail('Order not assigned to this salesman.', 403);
        }

        if ($order->status !== 'salesman_review') {
            return $this->fail('Only orders waiting for salesman review can be forwarded to admin.', 422);
        }

        $order->update(['status' => 'admin_review']);

        return $this->success(['order' => $order->fresh('items.product')], 'Order forwarded to admin.');
    }

    public function deliveries(Request $request): JsonResponse
    {
        $salesmanId = $this->salesman($request)->id;

        $dispatches = Dispatch::query()
            ->with('order.dealer.dealerProfile')
            ->whereHas('order', fn ($query) => $query->where('salesman_id', $salesmanId))
            ->latest()
            ->paginate(20);

        return $this->success(['dispatches' => $dispatches]);
    }
}
