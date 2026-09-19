<?php

namespace Tests\Unit;

use App\Contracts\Auth\ApiTokenGuardContract;
use App\Models\User;
use App\Services\Auth\ApiTokenGuard;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ApiTokenGuardTest extends TestCase
{
    /**
     * The middleware resolves the token once and stores the user on the
     * request, so a request carrying an already resolved user exercises the
     * role and status rules without touching the database.
     */
    private function requestFor(?User $user): Request
    {
        $request = Request::create('/api/dealer/orders', 'GET');

        if ($user) {
            $request->attributes->set('bawaskar_api_user', $user);
        }

        return $request;
    }

    private function user(string $role, string $status = 'active'): User
    {
        $user = new User(['name' => 'Test']);
        $user->forceFill(['id' => 1, 'role' => $role, 'status' => $status]);

        return $user;
    }

    public function test_the_middleware_role_names_match_the_user_role_constants(): void
    {
        // routes/api.php uses api.auth:customer, :dealer, :salesman, :admin
        $this->assertSame('customer', User::ROLE_CUSTOMER);
        $this->assertSame('dealer', User::ROLE_DEALER);
        $this->assertSame('salesman', User::ROLE_SALESMAN);
        $this->assertSame('admin', User::ROLE_ADMIN);
    }

    #[DataProvider('roleProvider')]
    public function test_a_token_only_opens_its_own_role(string $tokenRole, string $routeRole, bool $allowed): void
    {
        $resolved = (new ApiTokenGuard)->resolve($this->requestFor($this->user($tokenRole)), $routeRole);

        $this->assertSame($allowed, $resolved instanceof User);
    }

    public static function roleProvider(): array
    {
        return [
            'dealer on dealer route' => ['dealer', 'dealer', true],
            'customer on customer route' => ['customer', 'customer', true],
            'salesman on salesman route' => ['salesman', 'salesman', true],
            'admin on admin route' => ['admin', 'admin', true],
            'customer on dealer route' => ['customer', 'dealer', false],
            'dealer on admin route' => ['dealer', 'admin', false],
            'salesman on admin route' => ['salesman', 'admin', false],
            'dealer on customer route' => ['dealer', 'customer', false],
        ];
    }

    #[DataProvider('statusProvider')]
    public function test_only_an_active_account_is_accepted(string $status, bool $allowed): void
    {
        $resolved = (new ApiTokenGuard)->resolve($this->requestFor($this->user('dealer', $status)), 'dealer');

        $this->assertSame($allowed, $resolved instanceof User);
    }

    public static function statusProvider(): array
    {
        return [
            'active' => ['active', true],
            'pending dealer' => ['pending_approval', false],
            'inactive' => ['inactive', false],
        ];
    }

    public function test_no_token_is_rejected(): void
    {
        $this->assertNull((new ApiTokenGuard)->resolve($this->requestFor(null), 'dealer'));
    }

    public function test_the_guard_contract_is_what_the_middleware_depends_on(): void
    {
        $this->assertInstanceOf(ApiTokenGuardContract::class, new ApiTokenGuard);
    }
}
