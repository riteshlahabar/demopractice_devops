<?php

namespace App\Services\Admin\Access;

use App\Contracts\Admin\Access\AdminRoleServiceContract;
use App\Contracts\Admin\Access\AdminSectionCatalogContract;
use App\Models\Auth\AdminRole;
use Illuminate\Support\Facades\DB;

/**
 * Saves a role and its permission rows. Only sections and actions that exist
 * are stored, so a tampered form cannot grant anything the screen does not
 * offer. The Super Admin role keeps full access and cannot be deleted.
 */
final class AdminRoleService implements AdminRoleServiceContract
{
    public function __construct(private readonly AdminSectionCatalogContract $catalog) {}

    public function save(?AdminRole $role, array $data, array $permissions): AdminRole
    {
        return DB::transaction(function () use ($role, $data, $permissions): AdminRole {
            $role ??= new AdminRole;
            $role->fill([
                'name' => trim((string) $data['name']),
                'description' => $data['description'] ?? null,
            ])->save();

            if ($role->is_super) {
                return $role;
            }

            $rows = $this->rows($permissions);
            $role->permissions()->whereNotIn('section', array_keys($rows))->delete();

            foreach ($rows as $section => $flags) {
                $role->permissions()->updateOrCreate(['section' => $section], $flags);
            }

            return $role;
        });
    }

    public function permissionMap(?AdminRole $role): array
    {
        if (! $role) {
            return [];
        }

        return $role->permissions()->get()->mapWithKeys(fn ($permission): array => [
            $permission->section => array_values(array_filter(['view', 'create', 'edit', 'delete'], fn (string $action): bool => $permission->allows($action))),
        ])->all();
    }

    public function delete(AdminRole $role): void
    {
        if ($role->is_super) {
            throw new \DomainException('The Super Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            throw new \DomainException('Move the users of "'.$role->name.'" to another role before deleting it.');
        }

        $role->delete();
    }

    /**
     * @param  array<string, mixed>  $permissions
     * @return array<string, array{can_view: bool, can_create: bool, can_edit: bool, can_delete: bool}>
     */
    private function rows(array $permissions): array
    {
        $rows = [];

        foreach ($permissions as $section => $actions) {
            $section = (string) $section;
            if (! is_array($actions) || ! $this->catalog->has($section)) {
                continue;
            }

            $allowed = $this->catalog->actionsFor($section);
            $flags = [];
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $flags['can_'.$action] = in_array($action, $allowed, true) && ! empty($actions[$action]);
            }

            // Adding, editing or deleting is done from the list page, so they imply View.
            if ($flags['can_create'] || $flags['can_edit'] || $flags['can_delete']) {
                $flags['can_view'] = true;
            }

            if (in_array(true, $flags, true)) {
                $rows[$section] = $flags;
            }
        }

        return $rows;
    }
}
