<?php

namespace App\Http\Controllers\Admin\Access;

use App\Contracts\Admin\Access\AdminRoleServiceContract;
use App\Contracts\Admin\Access\AdminSectionCatalogContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Auth\AdminRole;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Roles with a View / Add / Edit / Delete grid for every sidebar section.
 * Listing, viewing and export come from the shared module screens.
 */
class AdminRoleController extends AdminModuleController
{
    protected string $moduleKey = 'admin-roles';

    public function __construct(
        AdminModuleServices $modules,
        private readonly AdminRoleServiceContract $roles,
        private readonly AdminSectionCatalogContract $catalog,
    ) {
        parent::__construct($modules);
    }

    public function create(Request $request): View
    {
        return $this->form(null);
    }

    public function edit(int|string $id): View
    {
        return $this->form($this->role($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $this->roles->save(null, $this->validateRequest($request, $this->module()), (array) $request->input('permissions', []));

        return redirect()->route('admin.admin-roles.edit', $role)->with('success', 'Role created successfully.');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $role = $this->role($id);
        $this->roles->save($role, $this->validateRequest($request, $this->module(), $role), (array) $request->input('permissions', []));

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $this->roles->delete($this->role($id));
        } catch (\DomainException $blocked) {
            return back()->with('error', $blocked->getMessage());
        }

        return redirect()->route('admin.admin-roles.index')->with('success', 'Role deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('selected_ids', []));

        try {
            // All or nothing: one role that cannot go keeps the others too.
            DB::transaction(fn () => AdminRole::query()->whereKey($ids)->get()->each(fn (AdminRole $role) => $this->roles->delete($role)));
        } catch (\DomainException $blocked) {
            return back()->with('error', $blocked->getMessage());
        }

        return back()->with('success', count($ids).' roles deleted successfully.');
    }

    private function form(?AdminRole $role): View
    {
        $module = $this->module();

        return view('admin.admin-roles.form', [
            'module' => $module,
            'pageTitle' => $role ? 'Edit Role' : 'Create Role',
            'breadcrumbs' => ['Admin', $module['label'], $role ? 'Edit' : 'Add'],
            'record' => $role,
            'groups' => $this->catalog->groups(),
            'granted' => old('permissions') !== null ? $this->oldPermissions() : $this->roles->permissionMap($role),
            'actionLabels' => (array) config('admin_access.action_labels', []),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    private function oldPermissions(): array
    {
        return collect((array) old('permissions', []))
            ->map(fn ($actions): array => array_keys(array_filter((array) $actions)))
            ->all();
    }

    private function role(int|string $id): AdminRole
    {
        $role = $this->findRecord($id);
        abort_unless($role instanceof AdminRole, 404);

        return $role;
    }
}
