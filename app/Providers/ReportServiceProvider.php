<?php

namespace App\Providers;

use App\Contracts\Admin\Reports\ReportExporterContract;
use App\Contracts\Admin\Reports\ReportFilterOptionsContract;
use App\Contracts\Admin\Reports\ReportRegistryContract;
use App\Services\Admin\Reports\Erp\DealerOutstandingReport;
use App\Services\Admin\Reports\Erp\DealerSalesReport;
use App\Services\Admin\Reports\Erp\GstSummaryReport;
use App\Services\Admin\Reports\Erp\OrderStatusReport;
use App\Services\Admin\Reports\Erp\PaymentsReport;
use App\Services\Admin\Reports\Erp\ProductSalesReport;
use App\Services\Admin\Reports\Erp\ReturnsReport;
use App\Services\Admin\Reports\Erp\SalesmanSalesReport;
use App\Services\Admin\Reports\Erp\SalesSummaryReport;
use App\Services\Admin\Reports\Erp\StockExpiryReport;
use App\Services\Admin\Reports\Hrms\AdvanceLoanReport;
use App\Services\Admin\Reports\Hrms\AttendanceReport;
use App\Services\Admin\Reports\Hrms\DealerVisitReport;
use App\Services\Admin\Reports\Hrms\EmployeeReport;
use App\Services\Admin\Reports\Hrms\ExpenseClaimReport;
use App\Services\Admin\Reports\Hrms\GpsAttendanceReport;
use App\Services\Admin\Reports\Hrms\IncentiveReport;
use App\Services\Admin\Reports\Hrms\LeaveReport;
use App\Services\Admin\Reports\Hrms\PayrollReport;
use App\Services\Admin\Reports\Hrms\PerformanceReport;
use App\Services\Admin\Reports\Hrms\SalaryRevisionReport;
use App\Services\Admin\Reports\Hrms\TargetCommissionReport;
use App\Services\Admin\Reports\Hrms\TourPlanReport;
use App\Services\Admin\Reports\ReportExporter;
use App\Services\Admin\Reports\ReportFilterOptions;
use App\Services\Admin\Reports\ReportRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Admin reports. A new report is a class implementing ReportContract added to
 * REPORTS — the controller, page, export and sidebar need no change.
 */
class ReportServiceProvider extends ServiceProvider
{
    /** Menu order within each section. */
    public const REPORTS = [
        SalesSummaryReport::class,
        ProductSalesReport::class,
        DealerSalesReport::class,
        SalesmanSalesReport::class,
        OrderStatusReport::class,
        PaymentsReport::class,
        DealerOutstandingReport::class,
        StockExpiryReport::class,
        ReturnsReport::class,
        GstSummaryReport::class,
        AttendanceReport::class,
        GpsAttendanceReport::class,
        LeaveReport::class,
        DealerVisitReport::class,
        TourPlanReport::class,
        ExpenseClaimReport::class,
        PayrollReport::class,
        SalaryRevisionReport::class,
        TargetCommissionReport::class,
        IncentiveReport::class,
        AdvanceLoanReport::class,
        PerformanceReport::class,
        EmployeeReport::class,
    ];

    public function register(): void
    {
        $this->app->tag(self::REPORTS, 'admin.reports');

        $this->app->singleton(ReportRegistryContract::class, fn ($app) => new ReportRegistry($app->tagged('admin.reports')));
        $this->app->bind(ReportExporterContract::class, ReportExporter::class);
        $this->app->bind(ReportFilterOptionsContract::class, ReportFilterOptions::class);
    }

    // The sidebar Reports menu (ReportMenu) is composed in AdminAccessServiceProvider,
    // which also removes the sections the signed-in role cannot open.
}
