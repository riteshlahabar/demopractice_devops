<?php

namespace App\Contracts\Storefront;

use App\Models\User;
use Illuminate\Http\Request;

interface StorefrontOrderContextContract
{
    public function context(Request $request, ?User $user): array;
}
