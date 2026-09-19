<?php

namespace App\Contracts\Admin\Reports;

/**
 * The list of available reports, looked up by key or by section.
 */
interface ReportRegistryContract
{
    /**
     * @return array<string, ReportContract>
     */
    public function all(): array;

    /**
     * @return array<string, ReportContract>
     */
    public function forSection(string $section): array;

    public function find(string $key): ?ReportContract;
}
