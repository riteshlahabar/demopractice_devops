<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Communication\SupportTicket;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function profile(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['user' => $user->load('customerProfile', 'addresses')]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'orders_count' => Order::query()->where('customer_id', $user->id)->count(),
            'pending_orders' => Order::query()->where('customer_id', $user->id)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'latest_orders' => Order::query()->with('items.product', 'dispatches')->where('customer_id', $user->id)->latest()->limit(5)->get(),
        ]);
    }

    public function addresses(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['addresses' => $user->addresses()->orderByDesc('is_default')->latest()->get()]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:12'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_default' => ['boolean'],
        ]);

        if (($validated['is_default'] ?? false) === true) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return $this->success(['address' => $address], 'Address saved.', 201);
    }

    public function support(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'ticket_no' => 'TKT'.now()->format('ymdHis').random_int(100, 999),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return $this->success(['ticket' => $ticket], 'Support ticket created.', 201);
    }
}
