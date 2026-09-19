<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\StorefrontOrderSessionContract;
use Illuminate\Http\Request;

final class StorefrontOrderSessionService implements StorefrontOrderSessionContract
{
    public function setLastOrderId(Request $request, int $orderId): void
    {
        $request->session()->put(StorefrontSessionKeys::LAST_ORDER_ID, $orderId);
    }

    public function lastOrderId(Request $request): ?int
    {
        $orderId = (int) $request->session()->get(StorefrontSessionKeys::LAST_ORDER_ID, 0);

        return $orderId > 0 ? $orderId : null;
    }
}
