<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Contracts\Admin\Reports\ReportContract;
use App\Contracts\Admin\Reports\ReportExporterContract;
use App\Contracts\Admin\Reports\ReportFilterOptionsContract;
use App\Contracts\Admin\Reports\ReportRegistryContract;
use App\Data\Admin\Reports\ReportFilters;
use App\Http\Controllers\Controller;
use App\Models\Field\AttendanceLog;
use App\Models\Field\Expense;
use App\Models\Field\SalarySlip;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use App\Services\Admin\Reports\ReportValueFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportRegistryContract $reports,
        private readonly ReportFilterOptionsContract $filterOptions,
        private readonly ReportExporterContract $exporter,
        private readonly ReportValueFormatter $formatter,
    ) {}

    public function index(): View
    {
        return view('admin.reports.index', [
            'pageTitle' => 'Reports Overview',
            'breadcrumbs' => ['Admin', 'Reports', 'Overview'],
            'summary' => [
                'b2b_sales' => Order::where('order_type', 'dealer')->whereNotIn('status', ['cancelled'])->sum('grand_total'),
                'b2c_sales' => Order::where('order_type', 'customer')->whereNotIn('status', ['cancelled'])->sum('grand_total'),
                'collections' => Payment::whereIn('status', ['paid', 'collected', 'verified'])->sum('amount'),
                'expenses' => Expense::where('status', 'approved')->sum('amount'),
                'salary' => SalarySlip::whereIn('status', ['approved', 'paid'])->sum('net_salary'),
                'attendance' => AttendanceLog::whereDate('attendance_date', today())->count(),
            ],
            'sections' => [
                'ERP Reports' => $this->reports->forSection(ReportContract::SECTION_ERP),
                'HRMS Reports' => $this->reports->forSection(ReportContract::SECTION_HRMS),
            ],
        ]);
    }

    public function show(Request $request, string $report): View
    {
        $definition = $this->find($report);
        $filters = ReportFilters::fromRequest($request);

        return view('admin.reports.show', [
            'pageTitle' => $definition->title(),
            'breadcrumbs' => ['Admin', 'Reports', $definition->section() === ReportContract::SECTION_HRMS ? 'HRMS' : 'ERP', $definition->title()],
            'report' => $definition,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions->for($definition->filters()),
            'result' => $definition->build($filters),
            'formatter' => $this->formatter,
        ]);
    }

    public function export(Request $request, string $report, string $format): Response
    {
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        $definition = $this->find($report);
        $filters = ReportFilters::fromRequest($request);
        $title = $definition->title().' '.$filters->from->format('d-m-Y').' to '.$filters->to->format('d-m-Y');

        return $this->exporter->download($format, $title, $definition->build($filters));
    }

    private function find(string $key): ReportContract
    {
        $report = $this->reports->find($key);
        abort_if($report === null, 404);

        return $report;
    }
}
