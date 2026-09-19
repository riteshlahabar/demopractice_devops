<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Contracts\Storefront\Session\StorefrontCartContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontOrderSessionContract;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorefrontCheckoutController extends Controller
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontCartContract $cart,
        private readonly StorefrontOrderSessionContract $orderSession
    ) {}

    public function placeOrder(
        Request $request,
        OrderWorkflowContract $orders
    ): RedirectResponse {
        $user = $this->identity->user($request);

        if (! $user || ! in_array($user->role, [User::ROLE_CUSTOMER, User::ROLE_DEALER], true)) {
            return redirect()->route('store.page', ['page' => 'login', 'redirect_to' => route('store.page', ['page' => 'checkout'])])
                ->with('error', 'Please log in before checkout.');
        }

        $cartSummary = $this->cart->summary($request);
        if (collect($cartSummary['items'])->isEmpty()) {
            return redirect()->route('store.page', ['page' => 'cart'])
                ->with('error', 'Your cart is empty.');
        }

        if ($cartSummary['has_issues']) {
            return redirect()->route('store.page', ['page' => 'cart'])
                ->with('error', 'Please update your cart before placing the order.');
        }

        $paymentMethods = $user->role === User::ROLE_DEALER
            ? ['cod', 'bank_transfer', 'upi', 'credit']
            : ['cod', 'bank_transfer', 'upi'];

        $validated = $request->validate([
            'address_type' => ['nullable', 'string', 'max:30'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_mobile' => ['required', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:12'],
            'payment_method' => ['required', Rule::in($paymentMethods)],
            'notes' => ['nullable', 'string'],
            'save_as_default' => ['nullable', 'boolean'],
        ], [
            'contact_name.required' => 'Please enter contact name.',
            'contact_mobile.required' => 'Please enter contact mobile number.',
            'address_line1.required' => 'Please enter address line 1.',
            'city.required' => 'Please enter city.',
            'state.required' => 'Please enter state.',
            'pincode.required' => 'Please enter pincode.',
            'payment_method.required' => 'Please select payment method.',
            'payment_method.in' => 'Selected payment method is not allowed for this account.',
        ]);

        $addressPayload = [
            'type' => $validated['address_type'] ?? 'shipping',
            'name' => $validated['contact_name'],
            'mobile' => $validated['contact_mobile'],
            'address_line1' => $validated['address_line1'],
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'is_default' => $request->boolean('save_as_default'),
        ];

        if ($addressPayload['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }

        Address::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $addressPayload['type'],
                'address_line1' => $addressPayload['address_line1'],
                'pincode' => $addressPayload['pincode'],
            ],
            $addressPayload
        );

        $checkoutData = [
            'contact_name' => $validated['contact_name'],
            'contact_mobile' => $validated['contact_mobile'],
            'address_type' => $validated['address_type'] ?? 'shipping',
            'address_line1' => $validated['address_line1'],
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'awaiting_confirmation',
        ];

        $items = $this->cart->checkoutItems($request);
        $order = $user->role === User::ROLE_DEALER
            ? $orders->createForDealer($user, $items, $validated['notes'] ?? null, $checkoutData)
            : $orders->createForCustomer($user, $items, $validated['notes'] ?? null, $checkoutData);

        $this->orderSession->setLastOrderId($request, $order->id);
        $this->cart->clear($request);

        return redirect()->route('store.page', ['page' => 'order-success'])
            ->with('success', 'Order placed successfully.');
    }
}
