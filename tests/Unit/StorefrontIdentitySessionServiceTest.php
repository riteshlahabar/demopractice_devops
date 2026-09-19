<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Session\Repositories\StorefrontSessionUserRepositoryContract;
use App\Models\User;
use App\Services\Storefront\Session\StorefrontIdentitySessionService;
use App\Services\Storefront\Session\StorefrontSessionKeys;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;

class StorefrontIdentitySessionServiceTest extends TestCase
{
    public function test_role_change_preserves_existing_session_cleanup_behavior(): void
    {
        $repository = new class implements StorefrontSessionUserRepositoryContract
        {
            public function find(int $userId, string $role): ?User
            {
                return null;
            }
        };

        $request = $this->requestWithSession();
        $request->session()->put(StorefrontSessionKeys::USER_ROLE, User::ROLE_CUSTOMER);
        $request->session()->put(StorefrontSessionKeys::CART, ['1:0' => ['quantity' => 1]]);
        $request->session()->put(StorefrontSessionKeys::WISHLIST, [1]);
        $request->session()->put(StorefrontSessionKeys::LAST_ORDER_ID, 99);

        $dealer = new User;
        $dealer->id = 8;
        $dealer->role = User::ROLE_DEALER;

        (new StorefrontIdentitySessionService($repository))->login($request, $dealer);

        $this->assertSame(8, $request->session()->get(StorefrontSessionKeys::USER_ID));
        $this->assertSame(User::ROLE_DEALER, $request->session()->get(StorefrontSessionKeys::USER_ROLE));
        $this->assertFalse($request->session()->has(StorefrontSessionKeys::CART));
        $this->assertFalse($request->session()->has(StorefrontSessionKeys::WISHLIST));
        $this->assertSame(99, $request->session()->get(StorefrontSessionKeys::LAST_ORDER_ID));
    }

    private function requestWithSession(): Request
    {
        $request = Request::create('/');
        $request->setLaravelSession(new Store('storefront-test', new ArraySessionHandler(120)));

        return $request;
    }
}
