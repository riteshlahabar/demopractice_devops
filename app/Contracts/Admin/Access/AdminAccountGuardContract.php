<?php

namespace App\Contracts\Admin\Access;

use App\Models\User;

/**
 * Stops changes that would lock the panel: editing your own role or status,
 * deleting yourself, or removing the last active Super Admin.
 */
interface AdminAccountGuardContract
{
    /**
     * @param  array<string, mixed>  $data
     * @return string|null error message, or null when the change is allowed
     */
    public function checkSave(?User $record, array $data, User $actor): ?string;

    public function checkDelete(User $record, User $actor): ?string;
}
