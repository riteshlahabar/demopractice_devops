<?php

namespace App\Services\Admin\Reports;

use App\Contracts\Admin\Reports\ReportContract;
use App\Contracts\Admin\Reports\ReportRegistryContract;

final class ReportRegistry implements ReportRegistryContract
{
    /**
     * @var array<string, ReportContract>
     */
    private array $reports = [];

    /**
     * @param  iterable<ReportContract>  $reports
     */
    public function __construct(iterable $reports)
    {
        foreach ($reports as $report) {
            $this->reports[$report->key()] = $report;
        }
    }

    public function all(): array
    {
        return $this->reports;
    }

    public function forSection(string $section): array
    {
        return array_filter($this->reports, fn (ReportContract $report): bool => $report->section() === $section);
    }

    public function find(string $key): ?ReportContract
    {
        return $this->reports[$key] ?? null;
    }
}
