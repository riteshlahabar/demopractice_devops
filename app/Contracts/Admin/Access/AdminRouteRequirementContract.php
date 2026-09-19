<?php

namespace App\Contracts\Admin\Access;

use App\Data\Admin\Access\AccessRequirement;
use Illuminate\Http\Request;

/**
 * Which section + action the current admin request needs.
 */
interface AdminRouteRequirementContract
{
    public function forRequest(Request $request): AccessRequirement;

    /**
     * Sections a module page maps to (two for a sales page whose channel is unknown).
     *
     * @return list<string>
     */
    public function sectionsForModule(string $module, ?string $channel): array;
}
