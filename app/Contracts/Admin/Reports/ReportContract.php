<?php

namespace App\Contracts\Admin\Reports;

use App\Data\Admin\Reports\ReportFilters;
use App\Data\Admin\Reports\ReportResult;

/**
 * One admin report: what it is called, which filters it offers, and how it
 * turns those filters into summary cards and table rows.
 */
interface ReportContract
{
    public const SECTION_ERP = 'erp';

    public const SECTION_HRMS = 'hrms';

    public function key(): string;

    public function title(): string;

    public function section(): string;

    public function description(): string;

    /**
     * Feather icon name used in the sidebar and on the report header.
     */
    public function icon(): string;

    /**
     * Filter names from ReportFilters::SUPPORTED, in display order.
     *
     * @return array<int, string>
     */
    public function filters(): array;

    public function build(ReportFilters $filters): ReportResult;
}
