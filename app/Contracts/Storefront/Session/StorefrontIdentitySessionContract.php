<?php

namespace App\Contracts\Storefront\Session;

use App\Models\User;
use Illuminate\Http\Request;

interface StorefrontIdentitySessionContract
{
    public function user(Request $request): ?User;

    public function audience(Request $request): string;

    public function login(Request $request, User $user): void;

    public function logout(Request $request): void;
}
