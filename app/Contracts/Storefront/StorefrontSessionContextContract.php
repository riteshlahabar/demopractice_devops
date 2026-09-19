<?php

namespace App\Contracts\Storefront;

use App\Models\User;
use Illuminate\Http\Request;

interface StorefrontSessionContextContract
{
    public function user(Request $request): ?User;

    public function audience(Request $request): string;

    public function cartSummary(Request $request): array;

    public function wishlistSummary(Request $request): array;

    public function lastOrderId(Request $request): ?int;
}
