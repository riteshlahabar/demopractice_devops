<?php

namespace Tests\Unit;

use App\Contracts\Admin\Access\AdminAccessContract;
use App\Contracts\Admin\Access\AdminRouteRequirementContract;
use App\Contracts\Admin\Access\AdminSectionCatalogContract;
use App\Contracts\Admin\Access\SalesChannelLookupContract;
use App\Data\Admin\Access\AccessRequirement;
use App\Models\Auth\AdminRole;
use App\Models\Auth\AdminRolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * DB-free: channel lookups are faked and roles are built in memory.
 */
class AdminAccessTest extends TestCase
{
    /** @var array<int|string, string> */
    private array $recordChannels = [];

    protected function setUp(): void
    {
        parent::setUp();

        $test = $this;
        $this->app->instance(SalesChannelLookupContract::class, new class($test) implements SalesChannelLookupContract
        {
            public function __construct(private AdminAccessTest $test) {}

            public function forRecords(string $module, array $ids): array
            {
                return array_intersect_key($this->test->channels(), array_flip($ids));
            }

            public function forOrder(int|string $orderId): ?string
            {
                return $this->test->channels()[$orderId] ?? null;
            }
        });
    }

    /**
     * @return array<int|string, string>
     */
    public function channels(): array
    {
        return $this->recordChannels;
    }

    public function test_every_admin_route_maps_to_a_real_section(): void
    {
        $catalog = $this->app->make(AdminSectionCatalogContract::class);
        $problems = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('admin.permission', $route->gatherMiddleware(), true)) {
                continue;
            }

            $requirement = $this->requirementFor($route);
            if ($requirement->open) {
                continue;
            }
            if ($requirement->isSuperOnly()) {
                $problems[] = 'unmapped '.$route->getName();

                continue;
            }

            foreach ($requirement->needs as [$section]) {
                if (! $catalog->has($section)) {
                    $problems[] = $route->getName().' -> unknown section '.$section;
                }
            }
        }

        $this->assertSame([], $problems);
    }

    public function test_sales_sections_are_split_by_channel(): void
    {
        $catalog = $this->app->make(AdminSectionCatalogContract::class);

        foreach (['orders', 'proforma-invoices', 'invoices', 'dispatches', 'returns'] as $module) {
            $this->assertTrue($catalog->has('customer-'.$module));
            $this->assertTrue($catalog->has('dealer-'.$module));
        }
        $this->assertTrue($catalog->has('report-sales-summary'));
        $this->assertSame(['view'], $catalog->actionsFor('report-sales-summary'));
        $this->assertNotContains('delete', $catalog->actionsFor('dealer-orders'));
    }

    public function test_record_channel_wins_over_the_type_link(): void
    {
        $this->recordChannels = [7 => 'dealer'];

        $requirement = $this->requirementFor(Route::getRoutes()->getByName('admin.orders.edit'), ['order' => 7], '?type=customer');

        $this->assertSame([['dealer-orders', 'edit']], $requirement->needs);
    }

    public function test_unknown_channel_needs_both_sections(): void
    {
        $requirement = $this->requirementFor(Route::getRoutes()->getByName('admin.invoices.index'));

        $this->assertSame([['customer-invoices', 'view'], ['dealer-invoices', 'view']], $requirement->needs);
    }

    public function test_listing_uses_the_type_link(): void
    {
        $requirement = $this->requirementFor(Route::getRoutes()->getByName('admin.returns.index'), [], '?type=customer');

        $this->assertSame([['customer-returns', 'view']], $requirement->needs);
    }

    public function test_role_allows_only_ticked_actions(): void
    {
        $access = $this->app->make(AdminAccessContract::class);
        $user = $this->admin(false, ['brands' => ['can_view' => true, 'can_edit' => true], 'customer-orders' => ['can_view' => true]]);

        $this->assertTrue($access->allows($user, 'brands', 'view'));
        $this->assertTrue($access->allows($user, 'brands', 'edit'));
        $this->assertFalse($access->allows($user, 'brands', 'delete'));
        $this->assertFalse($access->allows($user, 'units', 'view'));
        $this->assertSame(['view' => true, 'create' => false, 'edit' => false, 'delete' => false], $access->moduleAbilities($user, 'orders', 'customer'));
        $this->assertFalse($access->moduleAbilities($user, 'orders', null)['view']);
    }

    public function test_super_admin_and_inactive_accounts(): void
    {
        $access = $this->app->make(AdminAccessContract::class);

        $this->assertTrue($access->allows($this->admin(true), 'dealer-orders', 'edit'));

        $inactive = $this->admin(true);
        $inactive->status = 'inactive';
        $this->assertFalse($access->allows($inactive, 'brands', 'view'));
    }

    public function test_sidebar_keeps_only_viewable_sections(): void
    {
        $access = $this->app->make(AdminAccessContract::class);
        $menu = $access->menuFor($this->admin(false, ['dealer-orders' => ['can_view' => true]]));

        $this->assertCount(1, $menu);
        $this->assertSame('Sales', $menu[0]['label']);
        $this->assertSame('dealer-sales', $menu[0]['items'][0]['key']);
        $this->assertSame(['dealer-orders'], array_column($menu[0]['items'][0]['children'], 'key'));
    }

    /**
     * @param  array<string, array<string, bool>>  $permissions
     */
    private function admin(bool $super, array $permissions = []): User
    {
        $role = new AdminRole(['name' => $super ? 'Super' : 'Staff']);
        $role->id = $super ? 1 : 2;
        $role->is_super = $super;
        $role->setRelation('permissions', collect($permissions)->map(
            fn (array $flags, string $section) => new AdminRolePermission(['section' => $section] + $flags)
        )->values());

        $user = new User(['name' => 'Staff', 'role' => User::ROLE_ADMIN, 'status' => 'active']);
        $user->id = random_int(1000, 999999);
        $user->admin_role_id = $role->id;
        $user->setRelation('adminRole', $role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function requirementFor(RoutingRoute $route, array $parameters = [], string $query = ''): AccessRequirement
    {
        $uri = preg_replace_callback('/\{(\w+)\??\}/', fn (array $m): string => (string) ($parameters[$m[1]] ?? match ($m[1]) {
            'format' => 'excel', 'document' => 'order', 'report' => 'sales-summary', 'module' => 'brands', default => '0',
        }), $route->uri());

        $request = Request::create('/'.$uri.$query, $route->methods()[0]);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        return $this->app->make(AdminRouteRequirementContract::class)->forRequest($request);
    }
}
