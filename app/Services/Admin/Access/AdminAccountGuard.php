<?php

namespace App\Services\Admin\Access;

use App\Contracts\Admin\Access\AdminAccountGuardContract;
use App\Models\Auth\AdminRole;
use App\Models\User;

final class AdminAccountGuard implements AdminAccountGuardContract
{
    public function checkSave(?User $record, array $data, User $actor): ?string
    {
        if (! $record) {
            return null;
        }

        $newRoleId = (int) ($data['admin_role_id'] ?? $record->admin_role_id);
        $newStatus = (string) ($data['status'] ?? $record->status);

        if ($record->is($actor) && ($newRoleId !== (int) $record->admin_role_id || $newStatus !== 'active')) {
            return 'You cannot change your own role or deactivate your own account.';
        }

        $staysSuper = $newStatus === 'active' && (bool) AdminRole::query()->whereKey($newRoleId)->value('is_super');
        if (! $staysSuper && $this->isLastActiveSuperAdmin($record)) {
            return 'At least one active Super Admin is required.';
        }

        return null;
    }

    public function checkDelete(User $record, User $actor): ?string
    {
        if ($record->is($actor)) {
            return 'You cannot delete your own account.';
        }

        return $this->isLastActiveSuperAdmin($record) ? 'At least one active Super Admin is required.' : null;
    }

    private function isLastActiveSuperAdmin(User $user): bool
    {
        $superIds = AdminRole::query()->where('is_super', true)->pluck('id');

        if ($user->status !== 'active' || ! $superIds->contains($user->admin_role_id)) {
            return false;
        }

        return ! User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('status', 'active')
            ->whereIn('admin_role_id', $superIds)
            ->whereKeyNot($user->getKey())
            ->exists();
    }
}
