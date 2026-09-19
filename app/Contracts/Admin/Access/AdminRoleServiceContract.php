<?php

namespace App\Contracts\Admin\Access;

use App\Models\Auth\AdminRole;

interface AdminRoleServiceContract
{
    /**
     * @param  array{name: string, description?: string|null}  $data
     * @param  array<string, array<string, mixed>>  $permissions  section => [action => on]
     */
    public function save(?AdminRole $role, array $data, array $permissions): AdminRole;

    /**
     * section => list of allowed actions, for filling the form.
     *
     * @return array<string, list<string>>
     */
    public function permissionMap(?AdminRole $role): array;

    /**
     * @throws \DomainException when the role cannot be deleted
     */
    public function delete(AdminRole $role): void;
}
