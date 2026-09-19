<?php

namespace App\Contracts\Admin\Access;

use App\Models\User;

interface AdminAccessContract
{
    public function isSuper(User $user): bool;

    public function allows(User $user, string $section, string $action): bool;

    /**
     * What the user may do on a module page, for showing or hiding buttons.
     *
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    public function moduleAbilities(User $user, string $moduleKey, ?string $channel): array;

    /**
     * The sidebar with every section the user cannot view removed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function menuFor(User $user): array;

    /**
     * First page the user is allowed to open, or null when there is none.
     */
    public function firstAllowedUrl(User $user): ?string;
}
