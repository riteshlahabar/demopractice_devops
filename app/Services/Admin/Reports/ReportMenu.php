<?php

namespace App\Services\Admin\Reports;

use App\Contracts\Admin\Reports\ReportContract;
use App\Contracts\Admin\Reports\ReportRegistryContract;

/**
 * Builds the sidebar "Reports" menu from the registered reports, so adding a
 * report never means editing the menu config by hand.
 */
final class ReportMenu
{
    private const SECTIONS = [
        ReportContract::SECTION_ERP => ['key' => 'erp-reports', 'label' => 'ERP Reports'],
        ReportContract::SECTION_HRMS => ['key' => 'hrms-reports', 'label' => 'HRMS Reports'],
    ];

    public function __construct(private readonly ReportRegistryContract $reports) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        $items = [['key' => 'reports-overview', 'label' => 'Overview', 'route' => 'admin.reports.index']];

        foreach (self::SECTIONS as $section => $menu) {
            $items[] = $menu + [
                'children' => array_values(array_map(fn (ReportContract $report): array => [
                    'key' => 'report-'.$report->key(),
                    'label' => $report->title(),
                    'route' => 'admin.report.show',
                    'params' => ['report' => $report->key()],
                    'feather' => $report->icon(),
                ], $this->reports->forSection($section))),
            ];
        }

        return $items;
    }

    /**
     * The configured sidebar groups with the Reports group filled in.
     *
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    public function fill(array $groups): array
    {
        return array_map(
            fn (array $group): array => ($group['id'] ?? null) === 'reportsMenu' ? ['items' => $this->items()] + $group : $group,
            $groups,
        );
    }
}
