<?php

namespace App\Providers;

use App\Contracts\Admin\Access\AdminAccessContract;
use App\Contracts\Admin\Access\AdminAccountGuardContract;
use App\Contracts\Admin\Access\AdminRoleServiceContract;
use App\Contracts\Admin\Access\AdminRouteRequirementContract;
use App\Contracts\Admin\Access\AdminSectionCatalogContract;
use App\Contracts\Admin\Access\SalesChannelLookupContract;
use App\Models\User;
use App\Repositories\Admin\Access\EloquentSalesChannelLookup;
use App\Services\Admin\Access\AdminAccessService;
use App\Services\Admin\Access\AdminAccountGuard;
use App\Services\Admin\Access\AdminRoleService;
use App\Services\Admin\Access\AdminRouteRequirements;
use App\Services\Admin\Access\AdminSectionCatalog;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Admin roles & permissions: bindings, plus the view composers that trim the
 * sidebar and hide buttons the signed-in user is not allowed to use.
 */
class AdminAccessServiceProvider extends ServiceProvider
{
    public array $singletons = [
        AdminSectionCatalogContract::class => AdminSectionCatalog::class,
        AdminAccessContract::class => AdminAccessService::class,
        AdminRouteRequirementContract::class => AdminRouteRequirements::class,
        SalesChannelLookupContract::class => EloquentSalesChannelLookup::class,
        AdminRoleServiceContract::class => AdminRoleService::class,
        AdminAccountGuardContract::class => AdminAccountGuard::class,
    ];

    public function boot(): void
    {
        View::composer('admin.partials.startbar', function ($view): void {
            $user = auth()->user();
            $access = $this->app->make(AdminAccessContract::class);

            $view->with('sidebarGroups', $user instanceof User
                ? $access->menuFor($user)
                : $this->app->make(AdminSectionCatalogContract::class)->menu());
        });

        View::composer(['admin.shared.index', 'admin.shared.show', 'admin.people.show', 'admin.shared.table-toolbar'], function ($view): void {
            $user = auth()->user();
            $module = $view->getData()['module'] ?? null;
            if (! $user instanceof User || ! is_array($module) || empty($module['key'])) {
                return;
            }

            $type = request()->query('type');
            $view->with('moduleAccess', $this->app->make(AdminAccessContract::class)
                ->moduleAbilities($user, (string) $module['key'], is_string($type) ? $type : null));
        });
    }
}
