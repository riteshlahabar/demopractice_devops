<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\DealerProfile;
use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\SalesmanTarget;
use App\Models\Field\TourPlan;
use App\Models\Inventory\InventoryBatch;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return $this->erp();
    }

    public function erp(): View
    {
        $pendingOrderStatuses = ['salesman_review', 'admin_review', 'pending'];

        $stats = [
            ['label' => 'Approved Dealers', 'value' => DealerProfile::whereNotNull('approved_at')->count(), 'icon' => 'iconoir-shop', 'color' => 'success', 'trend' => 'B2B network'],
            ['label' => 'Customers', 'value' => User::where('role', User::ROLE_CUSTOMER)->count(), 'icon' => 'iconoir-user', 'color' => 'primary', 'trend' => 'Retail accounts'],
            ['label' => 'Products', 'value' => Product::count(), 'icon' => 'iconoir-box-iso', 'color' => 'info', 'trend' => 'Catalog master'],
            ['label' => 'Pending Orders', 'value' => Order::whereIn('status', $pendingOrderStatuses)->count(), 'icon' => 'iconoir-cart', 'color' => 'warning', 'trend' => 'Needs action'],
        ];

        return view('admin.dashboard.erp', [
            'pageTitle' => 'Dashboard ERP',
            'breadcrumbs' => ['Admin', 'Dashboard ERP'],
            'stats' => $stats,
            'recentOrders' => Order::with(['customer', 'dealer.dealerProfile', 'salesman'])->latest()->limit(8)->get(),
            'pendingDealerOrders' => Order::with(['dealer.dealerProfile', 'salesman'])->where('order_type', 'dealer')->whereIn('status', $pendingOrderStatuses)->latest()->limit(6)->get(),
            'pendingCustomerOrders' => Order::with(['customer'])->where('order_type', 'customer')->whereIn('status', $pendingOrderStatuses)->latest()->limit(6)->get(),
            'pendingDealers' => User::with('dealerProfile')->where('role', User::ROLE_DEALER)->where('status', 'pending_approval')->latest()->limit(5)->get(),
            'lowStock' => InventoryBatch::with('product', 'warehouse')->whereColumn('quantity', '<=', 'low_stock_alert')->limit(5)->get(),
            'todaySales' => Order::whereDate('created_at', today())->sum('grand_total'),
            'monthSales' => Order::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('grand_total'),
        ]);
    }

    public function hrms(): View
    {
        $today = today();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $stats = [
            ['label' => 'Active Salesmen', 'value' => User::where('role', User::ROLE_SALESMAN)->where('status', 'active')->count(), 'icon' => 'iconoir-community', 'color' => 'primary', 'trend' => 'Field team'],
            ['label' => 'Present Today', 'value' => AttendanceLog::whereDate('attendance_date', $today)->whereIn('status', ['present', 'late', 'half_day'])->count(), 'icon' => 'iconoir-check-circle', 'color' => 'success', 'trend' => 'Attendance marked'],
            ['label' => 'Pending Leave', 'value' => LeaveApplication::where('status', 'pending')->count(), 'icon' => 'iconoir-calendar-minus', 'color' => 'warning', 'trend' => 'Approval queue'],
            ['label' => 'Pending Expenses', 'value' => Expense::where('status', 'pending')->count(), 'icon' => 'iconoir-receive-dollars', 'color' => 'danger', 'trend' => 'Claims to review'],
        ];

        return view('admin.dashboard.hrms', [
            'pageTitle' => 'Dashboard HRMS',
            'breadcrumbs' => ['Admin', 'Dashboard HRMS'],
            'stats' => $stats,
            'todayAttendance' => AttendanceLog::with('salesman')->whereDate('attendance_date', $today)->latest('check_in_at')->limit(8)->get(),
            'recentVisits' => DealerVisit::with(['salesman', 'dealer.dealerProfile'])->latest('visited_at')->limit(6)->get(),
            'pendingLeaves' => LeaveApplication::with('salesman')->where('status', 'pending')->latest()->limit(5)->get(),
            'pendingExpenses' => Expense::with('salesman')->where('status', 'pending')->latest()->limit(5)->get(),
            'upcomingTours' => TourPlan::with('salesman')->whereDate('plan_date', '>=', $today)->orderBy('plan_date')->limit(5)->get(),
            'issuedAssets' => SalesmanAsset::where('status', 'issued')->count(),
            'monthlyPayroll' => SalarySlip::where('salary_year', now()->year)->where('salary_month', now()->month)->sum('net_salary'),
            'targetTotal' => SalesmanTarget::whereDate('period_start', '<=', $monthEnd)->whereDate('period_end', '>=', $monthStart)->sum('target_amount'),
            'achievedTotal' => SalesmanTarget::whereDate('period_start', '<=', $monthEnd)->whereDate('period_end', '>=', $monthStart)->sum('achieved_amount'),
            'pendingExpenseAmount' => Expense::where('status', 'pending')->sum('amount'),
        ]);
    }
}
