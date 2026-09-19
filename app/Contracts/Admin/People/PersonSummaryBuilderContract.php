<?php

namespace App\Contracts\Admin\People;

use App\Data\Admin\People\PersonSummary;
use App\Models\User;

/**
 * One implementation per role, so a new role adds a class instead of a branch.
 */
interface PersonSummaryBuilderContract
{
    public function role(): string;

    public function build(User $person): PersonSummary;
}
