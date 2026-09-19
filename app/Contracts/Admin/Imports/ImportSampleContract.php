<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: building the downloadable sample CSV for a module.
 */
interface ImportSampleContract
{
    /**
     * @param  array<string, mixed>  $moduleConfig
     * @return array<int, string>
     */
    public function headers(string $module, array $moduleConfig): array;

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    public function row(string $module, array $headers): array;
}
