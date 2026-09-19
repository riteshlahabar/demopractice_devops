<?php

namespace App\Services\Storefront\Session;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionUserRepositoryContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Models\User;
use Illuminate\Http\Request;

final class StorefrontIdentitySessionService implements StorefrontIdentitySessionContract
{
    public function __construct(
        private readonly StorefrontSessionUserRepositoryContract $users
    ) {}

    public function user(Request $request): ?User
    {
        $userId = (int) $request->session()->get(StorefrontSessionKeys::USER_ID, 0);
        $role = (string) $request->session()->get(StorefrontSessionKeys::USER_ROLE, '');

        if ($userId <= 0 || ! in_array($role, [User::ROLE_CUSTOMER, User::ROLE_DEALER], true)) {
            return null;
        }

        $user = $this->users->find($userId, $role);
        if (! $user || ! in_array($user->status, ['active', 'pending_approval'], true)) {
            $this->logout($request);

            return null;
        }

        return $user;
    }

    public function audience(Request $request): string
    {
        return $this->user($request)?->role === User::ROLE_DEALER ? 'dealer' : 'customer';
    }

    public function login(Request $request, User $user): void
    {
        $previousRole = (string) $request->session()->get(StorefrontSessionKeys::USER_ROLE, '');

        $request->session()->regenerate();
        $request->session()->put(StorefrontSessionKeys::USER_ID, $user->id);
        $request->session()->put(StorefrontSessionKeys::USER_ROLE, $user->role);

        if ($previousRole !== '' && $previousRole !== $user->role) {
            $request->session()->forget([
                StorefrontSessionKeys::CART,
                StorefrontSessionKeys::WISHLIST,
            ]);
        }
    }

    public function logout(Request $request): void
    {
        $request->session()->forget([
            StorefrontSessionKeys::USER_ID,
            StorefrontSessionKeys::USER_ROLE,
            StorefrontSessionKeys::CART,
            StorefrontSessionKeys::WISHLIST,
            StorefrontSessionKeys::LAST_ORDER_ID,
        ]);

        $request->session()->regenerateToken();
    }
}
