<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Http\Request;

/**
 * SRP: reading a module's definition out of config and naming its pages.
 */
interface ModuleDefinitionContract
{
    /**
     * @return array<string, mixed>
     */
    public function forKey(string $moduleKey): array;

    public function viewName(string $moduleKey, string $view): string;

    /**
     * @param  array<string, mixed>  $module
     */
    public function actionTitle(array $module, Request $request, string $action): string;

    /**
     * @param  array<string, mixed>  $module
     */
    public function pageTitle(array $module, Request $request): string;
}
