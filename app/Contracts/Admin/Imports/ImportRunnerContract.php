<?php

namespace App\Contracts\Admin\Imports;

use App\Data\Admin\ImportResult;

/**
 * SRP: running the import loop over already parsed rows.
 */
interface ImportRunnerContract
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|null>>  $rows
     * @param  array<string, mixed>  $moduleConfig
     * @param  array<string, mixed>  $forcedValues  Values pinned by the submenu.
     */
    public function run(string $module, array $moduleConfig, array $headers, array $rows, array $forcedValues = []): ImportResult;
}
