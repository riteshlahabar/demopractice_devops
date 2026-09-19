<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Http\Request;

/**
 * SRP: turning validated input into attributes ready to save - checkbox
 * booleans, untouched passwords, uploaded files and the slug fallback.
 */
interface ModuleInputContract
{
    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    public function prepare(array $validated, Request $request, array $module): array;
}
