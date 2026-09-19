<?php

namespace Tests\Feature;

use App\Contracts\Admin\Reports\ReportContract;
use App\Contracts\Admin\Reports\ReportRegistryContract;
use App\Data\Admin\Reports\ReportFilters;
use App\Services\Admin\Reports\ReportMenu;
use App\Services\Admin\Reports\ReportValueFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    public function test_ten_erp_and_thirteen_hrms_reports_are_registered_with_unique_keys(): void
    {
        $registry = app(ReportRegistryContract::class);

        $this->assertCount(23, $registry->all());
        $this->assertCount(10, $registry->forSection(ReportContract::SECTION_ERP));
        $this->assertCount(13, $registry->forSection(ReportContract::SECTION_HRMS));

        foreach ($registry->all() as $key => $report) {
            $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $key);
            $this->assertNotSame('', $report->title());
            $this->assertSame([], array_diff($report->filters(), ReportFilters::SUPPORTED), $key.' uses an unknown filter.');
        }

        $this->assertNull($registry->find('does-not-exist'));
    }

    public function test_filters_default_to_the_current_month_and_ignore_bad_input(): void
    {
        $filters = ReportFilters::fromRequest(Request::create('/', 'GET', [
            'from' => 'not-a-date', 'channel' => 'wholesale', 'period' => 'year', 'salesman_id' => '-4', 'expiry_days' => '999',
        ]));

        $this->assertSame(now()->startOfMonth()->toDateString(), $filters->from->toDateString());
        $this->assertSame(now()->toDateString(), $filters->to->toDateString());
        $this->assertNull($filters->channel);
        $this->assertSame('day', $filters->period);
        $this->assertNull($filters->salesmanId);
        $this->assertSame(30, $filters->expiryDays);
    }

    public function test_filters_swap_a_reversed_range_and_accept_all_batches(): void
    {
        $filters = ReportFilters::fromRequest(Request::create('/', 'GET', [
            'from' => '2026-09-30', 'to' => '2026-09-01', 'channel' => 'dealer', 'dealer_id' => '12', 'expiry_days' => 'all',
        ]));

        $this->assertSame('2026-09-01 00:00:00', $filters->from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-30 23:59:59', $filters->to->format('Y-m-d H:i:s'));
        $this->assertSame('dealer', $filters->channel);
        $this->assertSame(12, $filters->dealerId);
        $this->assertNull($filters->expiryDays);
        $this->assertSame('all', $filters->toQuery()['expiry_days']);
    }

    public function test_values_are_formatted_the_same_for_page_and_export(): void
    {
        $formatter = new ReportValueFormatter;

        $this->assertSame('Rs. 1,234.50', $formatter->format(1234.5, 'money'));
        $this->assertSame('12', $formatter->format(12.0, 'number'));
        $this->assertSame('12.50', $formatter->format(12.5, 'number'));
        $this->assertSame('66.7%', $formatter->format(66.66, 'percent'));
        $this->assertSame('Out For Delivery', $formatter->format('out_for_delivery', 'status'));
        $this->assertSame('-', $formatter->format(null));
    }

    public function test_sidebar_reports_menu_links_every_report_and_settings_replaces_system(): void
    {
        $groups = collect(app(ReportMenu::class)->fill(config('admin.groups')));

        $reports = $groups->firstWhere('id', 'reportsMenu');
        $children = collect($reports['items'])->pluck('children')->filter()->flatten(1);

        $this->assertSame('admin.reports.index', $reports['items'][0]['route']);
        $this->assertCount(23, $children);
        $this->assertTrue($children->every(fn (array $item): bool => $item['route'] === 'admin.report.show' && Route::has($item['route'])));

        $this->assertNotNull($groups->firstWhere('label', 'Settings'));
        $this->assertNull($groups->firstWhere('label', 'System'));
        $settingsRoutes = collect($groups->firstWhere('label', 'Settings')['items'])->pluck('route');
        $this->assertNotContains('admin.reports.index', $settingsRoutes);
    }
}
