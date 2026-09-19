<?php

namespace App\Contracts\Admin\Reports;

/**
 * Choices for the report filter dropdowns (salesmen, dealers, warehouses).
 */
interface ReportFilterOptionsContract
{
    /**
     * @param  array<int, string>  $filters
     * @return array<string, array<int|string, string>>
     */
    public function for(array $filters): array;
}
