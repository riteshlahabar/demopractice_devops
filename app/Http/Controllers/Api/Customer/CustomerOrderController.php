<?php

namespace App\Http\Controllers\Api\Customer;

use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\Sales\Orders\OrderItemImageAttacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends ApiController
{
    public function index(Request $request, OrderItemImageAttacher $images): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $orders = Order::query()
            ->with('items.product.images', 'items.variant', 'invoice', 'dispatches')
            ->where('customer_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        $images->attach($orders->getCollection());

        return $this->success(['orders' => $orders]);
    }

    public function store(Request $request, OrderWorkflowContract $orders): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.001',
            ],

            'contact_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_mobile' => [
                'required',
                'string',
                'max:20',
            ],

            'address_type' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address_line1' => [
                'required',
                'string',
                'max:255',
            ],

            'address_line2' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'state' => [
                'required',
                'string',
                'max:100',
            ],

            'pincode' => [
                'required',
                'string',
                'max:12',
            ],

            'payment_method' => [
                'required',
                'in:cod,bank_transfer,upi',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $checkoutData = [
            'contact_name' => $validated['contact_name'],

            'contact_mobile' => $validated['contact_mobile'],

            'address_type' => $validated['address_type']
                ?? 'shipping',

            'address_line1' => $validated['address_line1'],

            'address_line2' => $validated['address_line2']
                ?? null,

            'city' => $validated['city'],

            'state' => $validated['state'],

            'pincode' => $validated['pincode'],

            'payment_method' => $validated['payment_method'],

            'payment_status' => $validated['payment_method'] === 'cod'
                    ? 'pending'
                    : 'awaiting_confirmation',
        ];

        $order = $orders->createForCustomer(
            $user,
            $validated['items'],
            $validated['notes'] ?? null,
            $checkoutData
        );

        return $this->success(['order' => $order], 'Customer order sent to admin.', 201);
    }

    public function show(Request $request, Order $order, OrderItemImageAttacher $images): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        if ((int) $order->customer_id !== (int) $user->id) {
            return $this->fail('Order not found.', 404);
        }

        $order->load('items.product.images', 'items.variant', 'invoice', 'dispatches');
        $images->attach([$order]);

        return $this->success(['order' => $order]);
    }
}
