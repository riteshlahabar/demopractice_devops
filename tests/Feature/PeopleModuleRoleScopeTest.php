<?php

namespace Tests\Feature;

use App\Contracts\Admin\Modules\ModuleDefinitionContract;
use App\Contracts\Admin\Modules\ModuleQueryContract;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Dealers, Customers and Salesmen all read the users table, so each listing
 * (and the edit/delete/export actions that share its query) must be limited
 * to its own role — otherwise saving a customer from the Dealers page turns
 * that customer into a dealer.
 */
class PeopleModuleRoleScopeTest extends TestCase
{
    public function test_each_people_module_only_queries_its_own_role(): void
    {
        $modules = [
            'dealers' => User::ROLE_DEALER,
            'customers' => User::ROLE_CUSTOMER,
            'salesmen' => User::ROLE_SALESMAN,
        ];

        $queries = app(ModuleQueryContract::class);

        foreach ($modules as $moduleKey => $role) {
            $module = app(ModuleDefinitionContract::class)->forKey($moduleKey);

            foreach ([$queries->base($module), $queries->filtered(Request::create('/'), $module)] as $query) {
                $roleWheres = collect($query->getQuery()->wheres)->where('column', 'role');

                $this->assertCount(1, $roleWheres, $moduleKey.' must filter on role exactly once.');
                $this->assertSame($role, $roleWheres->first()['value'], $moduleKey.' shows the wrong role.');
            }
        }
    }

    public function test_collections_only_lists_payments_a_salesman_collected(): void
    {
        $module = app(ModuleDefinitionContract::class)->forKey('collections');
        $queries = app(ModuleQueryContract::class);

        foreach ([$queries->base($module), $queries->filtered(Request::create('/'), $module)] as $query) {
            $scope = collect($query->getQuery()->wheres)->first(fn (array $where): bool => $where['type'] === 'NotNull' && $where['column'] === 'collected_by');

            $this->assertNotNull($scope, 'Collections must be limited to collected payments.');
        }
    }
}
