<?php

namespace App\Contracts\Storefront\Session;

use Illuminate\Http\Request;

interface StorefrontOrderSessionContract
{
    public function setLastOrderId(Request $request, int $orderId): void;

    public function lastOrderId(Request $request): ?int;
}
