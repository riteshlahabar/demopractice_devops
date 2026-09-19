<?php

use App\Http\Controllers\Admin\Access\AdminRoleController;
use App\Http\Controllers\Admin\Access\AdminUserController;
use App\Http\Controllers\Admin\Assets\AssetController;
use App\Http\Controllers\Admin\Attendance\AttendanceController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Brands\BrandController;
use App\Http\Controllers\Admin\Categories\CategoryController;
use App\Http\Controllers\Admin\Collections\CollectionController;
use App\Http\Controllers\Admin\ContactMessages\ContactMessageController;
use App\Http\Controllers\Admin\Couriers\CourierController;
use App\Http\Controllers\Admin\Customers\CustomerController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Dealers\DealerController;
use App\Http\Controllers\Admin\DealerVisits\DealerVisitController;
use App\Http\Controllers\Admin\DeliveryAreas\DeliveryAreaController;
use App\Http\Controllers\Admin\Dispatches\DispatchController;
use App\Http\Controllers\Admin\EmailTemplates\EmailTemplateController;
use App\Http\Controllers\Admin\ExpenseCategories\ExpenseCategoryController;
use App\Http\Controllers\Admin\Expenses\ExpenseController;
use App\Http\Controllers\Admin\ExpenseSubcategories\ExpenseSubcategoryController;
use App\Http\Controllers\Admin\Hr\AllowanceTypeController;
use App\Http\Controllers\Admin\Hr\AnnouncementController;
use App\Http\Controllers\Admin\Hr\ApprovalWorkflowController;
use App\Http\Controllers\Admin\Hr\CommissionRuleController;
use App\Http\Controllers\Admin\Hr\DeductionTypeController;
use App\Http\Controllers\Admin\Hr\DepartmentController;
use App\Http\Controllers\Admin\Hr\DesignationController;
use App\Http\Controllers\Admin\Hr\EmployeeAllowanceController;
use App\Http\Controllers\Admin\Hr\EmployeeDeductionController;
use App\Http\Controllers\Admin\Hr\EmployeeDocumentController;
use App\Http\Controllers\Admin\Hr\EmployeeSkillController;
use App\Http\Controllers\Admin\Hr\HolidayController;
use App\Http\Controllers\Admin\Hr\HrmsSettingController;
use App\Http\Controllers\Admin\Hr\IncentiveRuleController;
use App\Http\Controllers\Admin\Hr\LeavePolicyController;
use App\Http\Controllers\Admin\Hr\PerformanceReviewController;
use App\Http\Controllers\Admin\Hr\ResignationController;
use App\Http\Controllers\Admin\Hr\SalaryAdvanceController;
use App\Http\Controllers\Admin\Hr\SalaryRevisionController;
use App\Http\Controllers\Admin\Hr\ShiftAssignmentController;
use App\Http\Controllers\Admin\Hr\ShiftController;
use App\Http\Controllers\Admin\Hr\TaskController;
use App\Http\Controllers\Admin\Hr\TrainingAttendanceController;
use App\Http\Controllers\Admin\Hr\TrainingProgramController;
use App\Http\Controllers\Admin\Imports\CommonImportController;
use App\Http\Controllers\Admin\InternalExpenses\InternalExpenseController;
use App\Http\Controllers\Admin\Inventory\InventoryController;
use App\Http\Controllers\Admin\Invoices\InvoiceController;
use App\Http\Controllers\Admin\Languages\LanguageController;
use App\Http\Controllers\Admin\Leaves\LeaveController;
use App\Http\Controllers\Admin\Notifications\NotificationController;
use App\Http\Controllers\Admin\Orders\OrderController;
use App\Http\Controllers\Admin\Outstanding\OutstandingController;
use App\Http\Controllers\Admin\Payments\PaymentController;
use App\Http\Controllers\Admin\Pricing\PricingController;
use App\Http\Controllers\Admin\ProductHomepageSettingItems\ProductHomepageSettingItemController;
use App\Http\Controllers\Admin\ProductHomepageSettings\ProductHomepageSettingController;
use App\Http\Controllers\Admin\ProductRelatedProducts\ProductRelatedProductController;
use App\Http\Controllers\Admin\Products\ProductController;
use App\Http\Controllers\Admin\Products\ProductImageController;
use App\Http\Controllers\Admin\Products\ProductTranslationController;
use App\Http\Controllers\Admin\ProductTypes\ProductTypeController;
use App\Http\Controllers\Admin\ProformaInvoices\ProformaInvoiceController;
use App\Http\Controllers\Admin\Reports\ReportController;
use App\Http\Controllers\Admin\Returns\ReturnController;
use App\Http\Controllers\Admin\Salary\SalaryController;
use App\Http\Controllers\Admin\SalesDocuments\SalesDocumentController;
use App\Http\Controllers\Admin\Salesmen\SalesmanController;
use App\Http\Controllers\Admin\Settings\CompanySettingController;
use App\Http\Controllers\Admin\StorefrontAbout\StorefrontAboutPageController;
use App\Http\Controllers\Admin\StorefrontAboutItems\StorefrontAboutItemController;
use App\Http\Controllers\Admin\StorefrontFaqs\StorefrontFaqController;
use App\Http\Controllers\Admin\StorefrontFooterLinks\StorefrontFooterLinkController;
use App\Http\Controllers\Admin\StorefrontServiceBlocks\StorefrontServiceBlockController;
use App\Http\Controllers\Admin\StorefrontTeamMembers\StorefrontTeamMemberController;
use App\Http\Controllers\Admin\StorefrontTopbarMessages\StorefrontTopbarMessageController;
use App\Http\Controllers\Admin\Support\SupportController;
use App\Http\Controllers\Admin\System\AuditLogController;
use App\Http\Controllers\Admin\System\BackupController;
use App\Http\Controllers\Admin\Targets\TargetController;
use App\Http\Controllers\Admin\TourPlans\TourPlanController;
use App\Http\Controllers\Admin\Translations\AppLanguageController;
use App\Http\Controllers\Admin\Translations\AppTranslationBatchController;
use App\Http\Controllers\Admin\Translations\TranslationController;
use App\Http\Controllers\Admin\Units\UnitController;
use App\Http\Controllers\Admin\Warehouses\WarehouseController;
use App\Http\Controllers\Admin\WebTranslations\WebTranslationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
    });

    Route::middleware(['auth', 'admin', 'admin.permission'])->group(function (): void {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('common-import/{module}/sample', [CommonImportController::class, 'sample'])->name('common-import.sample');
        Route::post('common-import/{module}', [CommonImportController::class, 'store'])->name('common-import.store');
        Route::get('/', [DashboardController::class, 'erp'])->name('dashboard');
        Route::get('dashboard/hrms', [DashboardController::class, 'hrms'])->name('dashboard.hrms');

        $resources = [
            'dealers' => DealerController::class, 'customers' => CustomerController::class, 'salesmen' => SalesmanController::class, 'couriers' => CourierController::class,
            'products' => ProductController::class, 'product-related-products' => ProductRelatedProductController::class, 'product-types' => ProductTypeController::class, 'categories' => CategoryController::class, 'brands' => BrandController::class, 'units' => UnitController::class, 'pricing' => PricingController::class, 'inventory' => InventoryController::class, 'warehouses' => WarehouseController::class, 'homepage-settings' => ProductHomepageSettingController::class, 'homepage-setting-items' => ProductHomepageSettingItemController::class,
            'orders' => OrderController::class, 'proforma-invoices' => ProformaInvoiceController::class, 'invoices' => InvoiceController::class, 'dispatches' => DispatchController::class, 'returns' => ReturnController::class,
            'payments' => PaymentController::class, 'collections' => CollectionController::class, 'outstanding' => OutstandingController::class,
            'internal-expenses' => InternalExpenseController::class, 'expense-categories' => ExpenseCategoryController::class, 'expense-subcategories' => ExpenseSubcategoryController::class,
            'attendance' => AttendanceController::class, 'dealer-visits' => DealerVisitController::class, 'tour-plans' => TourPlanController::class,
            'expenses' => ExpenseController::class, 'leaves' => LeaveController::class, 'salary' => SalaryController::class, 'targets' => TargetController::class, 'assets' => AssetController::class,
            'holidays' => HolidayController::class, 'shifts' => ShiftController::class, 'shift-assignments' => ShiftAssignmentController::class, 'announcements' => AnnouncementController::class,
            'employee-documents' => EmployeeDocumentController::class, 'salary-advances' => SalaryAdvanceController::class, 'salary-revisions' => SalaryRevisionController::class, 'performance-reviews' => PerformanceReviewController::class,
            'departments' => DepartmentController::class, 'designations' => DesignationController::class, 'leave-policies' => LeavePolicyController::class, 'approval-workflows' => ApprovalWorkflowController::class,
            'allowance-types' => AllowanceTypeController::class, 'deduction-types' => DeductionTypeController::class, 'employee-allowances' => EmployeeAllowanceController::class, 'employee-deductions' => EmployeeDeductionController::class,
            'resignations' => ResignationController::class, 'tasks' => TaskController::class, 'incentive-rules' => IncentiveRuleController::class, 'commission-rules' => CommissionRuleController::class,
            'training-programs' => TrainingProgramController::class, 'training-attendances' => TrainingAttendanceController::class, 'employee-skills' => EmployeeSkillController::class,
            'audit-logs' => AuditLogController::class, 'backups' => BackupController::class,
            'storefront-footer-links' => StorefrontFooterLinkController::class, 'storefront-service-blocks' => StorefrontServiceBlockController::class, 'delivery-areas' => DeliveryAreaController::class,
            'storefront-topbar-messages' => StorefrontTopbarMessageController::class, 'storefront-faqs' => StorefrontFaqController::class,
            'storefront-about-items' => StorefrontAboutItemController::class, 'storefront-team-members' => StorefrontTeamMemberController::class,
            'contact-messages' => ContactMessageController::class,
            'web-translations' => WebTranslationController::class,
            'admin-users' => AdminUserController::class, 'admin-roles' => AdminRoleController::class,
            'notifications' => NotificationController::class, 'languages' => LanguageController::class, 'translations' => TranslationController::class, 'support' => SupportController::class,
        ];
        Route::post('products/translate', [ProductTranslationController::class, 'store'])->name('products.translate');
        Route::get('settings/company', [CompanySettingController::class, 'edit'])->name('company-settings.edit');
        Route::put('settings/company', [CompanySettingController::class, 'update'])->name('company-settings.update');
        Route::get('storefront/about-page', [StorefrontAboutPageController::class, 'edit'])->name('storefront-about.edit');
        Route::put('storefront/about-page', [StorefrontAboutPageController::class, 'update'])->name('storefront-about.update');
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::delete('products/{product}/field-image', [ProductImageController::class, 'destroyField'])->name('products.field-image.destroy');
        Route::get('attendance/bulk', [AttendanceController::class, 'bulk'])->name('attendance.bulk');
        Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore'])->name('attendance.bulk.store');
        foreach ($resources as $uri => $controller) {
            Route::get($uri.'/export/{format}', [$controller, 'export'])->whereIn('format', ['excel', 'pdf'])->name($uri.'.export');
            Route::delete($uri.'/bulk-destroy', [$controller, 'bulkDestroy'])->name($uri.'.bulk-destroy');
            Route::resource($uri, $controller);
        }

        Route::post('dealers/{dealer}/approve', [DealerController::class, 'approve'])->name('dealers.approve');
        Route::post('orders/{id}/convert-to-proforma', [OrderController::class, 'convertToProforma'])->name('orders.convert-to-proforma');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('proforma-invoices/{id}/convert-to-invoice', [ProformaInvoiceController::class, 'convertToInvoice'])->name('proforma-invoices.convert-to-invoice');
        Route::get('sales-documents/{document}/{id}/print', [SalesDocumentController::class, 'print'])->whereIn('document', ['order', 'proforma', 'invoice'])->name('sales-documents.print');
        Route::get('sales-documents/{document}/{id}/pdf', [SalesDocumentController::class, 'pdf'])->whereIn('document', ['order', 'proforma', 'invoice'])->name('sales-documents.pdf');
        Route::post('expenses/{expense}/decision', [ExpenseController::class, 'decision'])->name('expenses.decision');
        Route::post('leaves/{leave}/decision', [LeaveController::class, 'decision'])->name('leaves.decision');
        Route::get('employee-documents/{employee_document}/download', [EmployeeDocumentController::class, 'download'])->name('employee-documents.download');
        Route::get('training-attendances/{training_attendance}/download', [TrainingAttendanceController::class, 'download'])->name('training-attendances.download');
        Route::post('salary/generate', [SalaryController::class, 'generate'])->name('salary.generate');
        Route::post('translations/translate-batch', AppTranslationBatchController::class)->name('translations.translate-batch');
        Route::post('resignations/{resignation}/suggest-settlement', [ResignationController::class, 'suggestSettlement'])->name('resignations.suggest-settlement');
        Route::post('backups/run', [BackupController::class, 'run'])->name('backups.run');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::get('hrms/settings', [HrmsSettingController::class, 'edit'])->name('hrms-settings.edit');
        Route::put('hrms/settings', [HrmsSettingController::class, 'update'])->name('hrms-settings.update');
        Route::get('translation/app-languages', [AppLanguageController::class, 'edit'])->name('app-languages.edit');
        Route::put('translation/app-languages', [AppLanguageController::class, 'update'])->name('app-languages.update');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}/export/{format}', [ReportController::class, 'export'])->where('report', '[a-z0-9-]+')->whereIn('format', ['excel', 'pdf'])->name('report.export');
        Route::get('reports/{report}', [ReportController::class, 'show'])->where('report', '[a-z0-9-]+')->name('report.show');
        Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('email-templates/{template}', [EmailTemplateController::class, 'show'])->name('email-templates.show');
    });
});
