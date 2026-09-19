<?php

namespace Tests\Unit;

use App\Contracts\Storefront\Repositories\StorefrontOrderRepositoryContract;
use App\Contracts\Storefront\StorefrontSessionContextContract;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\Storefront\StorefrontOrderContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class StorefrontOrderContextServiceTest extends TestCase
{
    public function test_requested_order_keeps_precedence_over_last_and_latest_orders(): void
    {
        $recent = new Order;
        $recent->id = 1;
        $last = new Order;
        $last->id = 10;
        $tracked = new Order;
        $tracked->id = 11;
        $latest = new Order;
        $latest->id = 12;

        $orders = new class($recent, $last, $tracked, $latest) implements StorefrontOrderRepositoryContract
        {
            public function __construct(
                private readonly Order $recent,
                private readonly Order $last,
                private readonly Order $tracked,
                private readonly Order $latestOrder
            ) {}

            public function recent(User $user): Collection
            {
                return collect([$this->recent]);
            }

            public function find(User $user, int $orderId): ?Order
            {
                return $this->last;
            }

            public function tracked(User $user, string $requestedOrder): ?Order
            {
                return $this->tracked;
            }

            public function latest(User $user): ?Order
            {
                return $this->latestOrder;
            }
        };

        $session = new class implements StorefrontSessionContextContract
        {
            public function user(Request $request): ?User
            {
                return null;
            }

            public function audience(Request $request): string
            {
                return 'customer';
            }

            public function cartSummary(Request $request): array
            {
                return [];
            }

            public function wishlistSummary(Request $request): array
            {
                return [];
            }

            public function lastOrderId(Request $request): ?int
            {
                return 10;
            }
        };

        $user = new User;
        $user->id = 7;
        $request = Request::create('/order-tracking', 'GET', ['order' => 'SO-11']);
        $result = (new StorefrontOrderContextService($orders, $session))->context($request, $user);

        $this->assertSame([1], $result['orders']->pluck('id')->all());
        $this->assertSame($last, $result['lastOrder']);
        $this->assertSame($tracked, $result['trackedOrder']);
    }
}
