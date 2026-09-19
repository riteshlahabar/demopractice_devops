<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * SRP: building the listing query for a config driven admin module -
 * eager loads, search, status, submenu and date filters.
 */
interface ModuleQueryContract
{
    /**
     * @param  array<string, mixed>  $module
     */
    public function base(array $module): Builder;

    /**
     * @param  array<string, mixed>  $module
     */
    public function filtered(Request $request, array $module): Builder;
}
