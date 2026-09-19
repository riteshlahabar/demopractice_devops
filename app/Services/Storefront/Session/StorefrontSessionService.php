<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\StorefrontCartContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\Session\StorefrontOrderSessionContract;
use App\Contracts\Storefront\Session\StorefrontWishlistContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Models\User;
use Illuminate\Http\Request;

final class StorefrontSessionService implements StorefrontSessionContextContract
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly StorefrontCartContract $cart,
        private readonly StorefrontWishlistContract $wishlist,
        private readonly StorefrontOrderSessionContract $orders
    ) {}

    public function user(Request $request): ?User
    {
        return $this->identity->user($request);
    }

    public function audience(Request $request): string
    {
        return $this->identity->audience($request);
    }

    public function cartSummary(Request $request): array
    {
        return $this->cart->summary($request);
    }

    public function wishlistSummary(Request $request): array
    {
        return $this->wishlist->summary($request);
    }

    public function lastOrderId(Request $request): ?int
    {
        return $this->orders->lastOrderId($request);
    }
}
