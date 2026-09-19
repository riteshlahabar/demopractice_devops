<?php

namespace App\Services\Sales\Orders;

use App\Contracts\Sales\Orders\OrderCheckoutMapperContract;

final class OrderCheckoutMapper implements OrderCheckoutMapperContract
{
    public function map(array $checkoutData): array
    {
        return [
            'contact_name' => $checkoutData['contact_name'] ?? null,
            'contact_mobile' => $checkoutData['contact_mobile'] ?? null,
            'address_type' => $checkoutData['address_type'] ?? null,
            'address_line1' => $checkoutData['address_line1'] ?? null,
            'address_line2' => $checkoutData['address_line2'] ?? null,
            'city' => $checkoutData['city'] ?? null,
            'state' => $checkoutData['state'] ?? null,
            'pincode' => $checkoutData['pincode'] ?? null,
            'payment_method' => $checkoutData['payment_method'] ?? null,
            'payment_status' => $checkoutData['payment_status'] ?? 'pending',
        ];
    }
}
