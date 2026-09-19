<?php

use App\Http\Controllers\Api\Admin\AdminCatalogController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminPeopleController;
use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\Auth\DealerAuthController;
use App\Http\Controllers\Api\Auth\DealerRegistrationController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\StaffAuthController;
use App\Http\Controllers\Api\Catalog\CategoryCatalogController;
use App\Http\Controllers\Api\Catalog\HomepageCatalogController;
use App\Http\Controllers\Api\Catalog\ProductCatalogController;
use App\Http\Controllers\Api\Catalog\ProductReviewController;
use App\Http\Controllers\Api\Catalog\TranslationCatalogController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Customer\CustomerOfferController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;
use App\Http\Controllers\Api\Customer\CustomerReviewController;
use App\Http\Controllers\Api\Customer\CustomerWishlistController;
use App\Http\Controllers\Api\Dealer\DealerController;
use App\Http\Controllers\Api\Dealer\DealerOrderController;
use App\Http\Controllers\Api\Dealer\DealerOutstandingController;
use App\Http\Controllers\Api\Dealer\DealerPaymentController;
use App\Http\Controllers\Api\Dealer\DealerReportController;
use App\Http\Controllers\Api\Localization\AppTranslationController;
use App\Http\Controllers\Api\Location\LocationController;
use App\Http\Controllers\Api\Payments\OnlinePaymentController;
use App\Http\Controllers\Api\Salesman\SalesmanAdvanceController;
use App\Http\Controllers\Api\Salesman\SalesmanAnnouncementController;
use App\Http\Controllers\Api\Salesman\SalesmanAttendanceController;
use App\Http\Controllers\Api\Salesman\SalesmanCalendarController;
use App\Http\Controllers\Api\Salesman\SalesmanDashboardController;
use App\Http\Controllers\Api\Salesman\SalesmanDocumentController;
use App\Http\Controllers\Api\Salesman\SalesmanExitController;
use App\Http\Controllers\Api\Salesman\SalesmanFinanceController;
use App\Http\Controllers\Api\Salesman\SalesmanHrController;
use App\Http\Controllers\Api\Salesman\SalesmanLeaveBalanceController;
use App\Http\Controllers\Api\Salesman\SalesmanOrderController;
use App\Http\Controllers\Api\Salesman\SalesmanPayslipController;
use App\Http\Controllers\Api\Salesman\SalesmanPerformanceController;
use App\Http\Controllers\Api\Salesman\SalesmanProfileController;
use App\Http\Controllers\Api\Salesman\SalesmanSalaryRevisionController;
use App\Http\Controllers\Api\Salesman\SalesmanSkillController;
use App\Http\Controllers\Api\Salesman\SalesmanTaskController;
use App\Http\Controllers\Api\Salesman\SalesmanTrainingController;
use App\Http\Controllers\Api\Shared\ChangePasswordController;
use App\Http\Controllers\Api\Shared\DeviceTokenController;
use App\Http\Controllers\Api\Shared\InvoiceController;
use App\Http\Controllers\Api\Shared\NotificationController;
use App\Http\Controllers\Api\Shared\OrderTrackingController;
use App\Http\Controllers\Api\Shared\ProfileUpdateController;
use App\Http\Controllers\Api\Shared\ReturnRequestController;
use Illuminate\Support\Facades\Route;

/**
 * Endpoints shared by the customer, dealer and salesman apps.
 *
 * The controllers behind these scope every query to the caller, so mounting
 * the same routes under three prefixes cannot expose one role's data to
 * another. Registering them once keeps the three apps on identical payloads.
 */
$registerSharedAccountRoutes = static function (): void {
    Route::get('change-password', [ChangePasswordController::class, 'show']);
    Route::post('change-password', [ChangePasswordController::class, 'update']);

    Route::post('profile', [ProfileUpdateController::class, 'update']);
    Route::post('profile/photo', [ProfileUpdateController::class, 'photo']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read', [NotificationController::class, 'markRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('device-token', [DeviceTokenController::class, 'store']);
    Route::post('device-token/remove', [DeviceTokenController::class, 'destroy']);

    Route::get('orders/{order}/tracking', [OrderTrackingController::class, 'show']);

    // Opening an online payment is shared: a customer and a dealer pay for
    // an order the same way, and the ownership scope decides whose order it
    // is, so there is one implementation rather than two.
    Route::post('orders/{order}/pay', [OnlinePaymentController::class, 'start']);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);

    Route::get('returns', [ReturnRequestController::class, 'index']);
    Route::post('returns', [ReturnRequestController::class, 'store']);
    Route::get('returns/{returnRequest}', [ReturnRequestController::class, 'show']);
};

$registerBawaskarApi = static function () use ($registerSharedAccountRoutes): void {
    Route::get('health', static fn () => response()->json([
        'success' => true,
        'message' => 'Bawaskar ERP API is running.',
        'data' => ['timestamp' => now()->toIso8601String()],
    ]))->middleware('throttle:api');

    Route::prefix('auth')->group(function (): void {
        Route::post('otp/request', [OtpController::class, 'request'])->middleware('throttle:otp');

        Route::middleware('throttle:login')->group(function (): void {
            // Firebase phone sign-in. The app verifies the number with
            // Google and posts the ID token; these replace the otp/verify
            // routes below, which stay only until the old builds are gone.
            Route::post('customer/firebase/verify', [CustomerAuthController::class, 'verifyFirebase']);
            Route::post('dealer/firebase/verify', [DealerAuthController::class, 'verifyFirebase']);

            Route::post('customer/otp/verify', [CustomerAuthController::class, 'verifyOtp']);
            Route::post('customer/login', [CustomerAuthController::class, 'login']);
            Route::post('customer/register', [CustomerAuthController::class, 'register']);
            Route::post('dealer/otp/verify', [DealerAuthController::class, 'verifyOtp']);
            Route::post('dealer/register', [DealerRegistrationController::class, 'register']);
            Route::post('dealer/login', [DealerAuthController::class, 'login']);
            Route::post('salesman/login', [StaffAuthController::class, 'salesmanLogin']);
            Route::post('admin/login', [StaffAuthController::class, 'adminLogin']);
        });

        Route::post('logout', [StaffAuthController::class, 'logout'])->middleware('throttle:api');
    });

    Route::middleware('throttle:api')->group(function () use ($registerSharedAccountRoutes): void {
        // Public, but a valid token is still read so an approved dealer gets
        // the dealer catalog instead of a 401.
        Route::middleware('api.identify')->group(function (): void {
            Route::get('catalog/categories', [CategoryCatalogController::class, 'index']);
            Route::get('catalog/products', [ProductCatalogController::class, 'index']);
            Route::get('catalog/products/{product}/reviews', [ProductReviewController::class, 'index']);
            Route::get('catalog/homepage', [HomepageCatalogController::class, 'index']);
        });
        Route::get('locations/states', [LocationController::class, 'states']);
        Route::get('locations/districts', [LocationController::class, 'districts']);
        Route::get('locations/subdistricts', [LocationController::class, 'subdistricts']);
        Route::get('translations', [TranslationCatalogController::class, 'index']);
        Route::post('translations/sync', [TranslationCatalogController::class, 'sync'])->middleware('throttle:otp');
        // Mobile-app strings (app_translations): read-only fetch + key registration.
        Route::get('app-translations', [AppTranslationController::class, 'index']);
        Route::post('app-translations/register', [AppTranslationController::class, 'register']);

        Route::prefix('customer')->middleware('api.auth:customer')->group(function () use ($registerSharedAccountRoutes): void {
            Route::get('dashboard', [CustomerController::class, 'dashboard']);
            Route::get('profile', [CustomerController::class, 'profile']);
            Route::get('addresses', [CustomerController::class, 'addresses']);
            Route::post('addresses', [CustomerController::class, 'storeAddress']);
            Route::post('support', [CustomerController::class, 'support']);
            Route::get('orders', [CustomerOrderController::class, 'index']);
            Route::post('orders', [CustomerOrderController::class, 'store']);
            Route::get('orders/{order}', [CustomerOrderController::class, 'show']);

            Route::get('wishlist', [CustomerWishlistController::class, 'index']);
            Route::post('wishlist', [CustomerWishlistController::class, 'store']);
            Route::delete('wishlist/{product}', [CustomerWishlistController::class, 'destroy']);

            Route::get('offers', [CustomerOfferController::class, 'index']);
            Route::post('offers/validate', [CustomerOfferController::class, 'validateCoupon']);

            Route::get('reviews', [CustomerReviewController::class, 'index']);
            Route::post('reviews', [CustomerReviewController::class, 'store']);

            $registerSharedAccountRoutes();
        });

        Route::prefix('dealer')->middleware('api.auth:dealer')->group(function () use ($registerSharedAccountRoutes): void {
            Route::get('dashboard', [DealerController::class, 'dashboard']);
            Route::get('profile', [DealerController::class, 'profile']);
            Route::get('addresses', [DealerController::class, 'addresses']);
            Route::post('addresses', [DealerController::class, 'storeAddress']);
            Route::post('support', [DealerController::class, 'support']);
            Route::get('statements', [DealerController::class, 'statements']);
            Route::get('orders', [DealerOrderController::class, 'index']);
            Route::post('orders', [DealerOrderController::class, 'store']);
            Route::get('orders/{order}', [DealerOrderController::class, 'show']);

            Route::get('outstanding', [DealerOutstandingController::class, 'index']);
            Route::get('credit-limit', [DealerOutstandingController::class, 'index']);
            Route::get('ledger', [DealerOutstandingController::class, 'ledger']);
            Route::get('payments', [DealerPaymentController::class, 'index']);

            Route::get('reports/orders', [DealerReportController::class, 'orders']);
            Route::get('reports/sales', [DealerReportController::class, 'sales']);

            $registerSharedAccountRoutes();
        });

        Route::prefix('salesman')->middleware('api.auth:salesman')->group(function () use ($registerSharedAccountRoutes): void {
            Route::get('dashboard', [SalesmanDashboardController::class, 'dashboard']);
            Route::get('dealers', [SalesmanDashboardController::class, 'dealers']);

            Route::post('attendance/check-in', [SalesmanAttendanceController::class, 'checkIn']);
            Route::post('attendance/check-out', [SalesmanAttendanceController::class, 'checkOut']);
            Route::get('attendance', [SalesmanCalendarController::class, 'attendance']);
            Route::get('visits', [SalesmanAttendanceController::class, 'visits']);
            Route::post('visits', [SalesmanAttendanceController::class, 'storeVisit']);

            Route::get('orders', [SalesmanOrderController::class, 'index']);
            Route::post('orders', [SalesmanOrderController::class, 'store']);
            Route::post('orders/{order}/forward-to-admin', [SalesmanOrderController::class, 'forwardToAdmin']);
            Route::get('deliveries', [SalesmanOrderController::class, 'deliveries']);

            Route::post('collections', [SalesmanFinanceController::class, 'collectPayment']);
            Route::get('expenses', [SalesmanFinanceController::class, 'expenses']);
            Route::post('expenses', [SalesmanFinanceController::class, 'storeExpense']);
            Route::get('salary', [SalesmanFinanceController::class, 'salary']);
            Route::get('targets', [SalesmanFinanceController::class, 'targets']);

            Route::get('payslips', [SalesmanPayslipController::class, 'index']);
            Route::get('payslips/{payslip}', [SalesmanPayslipController::class, 'show']);
            Route::get('advances', [SalesmanAdvanceController::class, 'index']);
            Route::post('advances', [SalesmanAdvanceController::class, 'store']);
            Route::get('loans', [SalesmanAdvanceController::class, 'index']);
            Route::get('incentives', [SalesmanPerformanceController::class, 'incentives']);
            Route::get('performance', [SalesmanPerformanceController::class, 'reviews']);

            Route::get('leaves', [SalesmanHrController::class, 'leaves']);
            Route::post('leaves', [SalesmanHrController::class, 'storeLeave']);
            Route::get('leaves/balance', [SalesmanLeaveBalanceController::class, 'index']);
            Route::get('assets', [SalesmanHrController::class, 'assets']);
            Route::get('tour-plans', [SalesmanHrController::class, 'tourPlans']);

            Route::get('holidays', [SalesmanCalendarController::class, 'holidays']);
            Route::get('shifts', [SalesmanCalendarController::class, 'shift']);
            Route::get('announcements', [SalesmanAnnouncementController::class, 'index']);
            Route::get('documents', [SalesmanDocumentController::class, 'index']);

            Route::get('profile', [SalesmanProfileController::class, 'profile']);
            Route::post('support', [SalesmanProfileController::class, 'support']);

            Route::get('salary-revisions', [SalesmanSalaryRevisionController::class, 'index']);

            Route::get('tasks', [SalesmanTaskController::class, 'index']);
            Route::post('tasks/{task}', [SalesmanTaskController::class, 'update']);

            Route::get('skills', [SalesmanSkillController::class, 'index']);

            Route::get('trainings', [SalesmanTrainingController::class, 'index']);
            Route::get('trainings/{attendance}/certificate', [SalesmanTrainingController::class, 'certificate']);

            Route::get('resignation', [SalesmanExitController::class, 'index']);
            Route::post('resignation', [SalesmanExitController::class, 'store']);

            $registerSharedAccountRoutes();
        });

        Route::prefix('admin')->middleware('api.auth:admin')->group(function (): void {
            Route::get('dashboard', [AdminDashboardController::class, 'dashboard']);

            Route::post('salesmen', [AdminPeopleController::class, 'createSalesman']);
            Route::get('dealers', [AdminPeopleController::class, 'dealers']);
            Route::post('dealers/{dealer}/approve', [AdminPeopleController::class, 'approveDealer']);
            Route::post('dealers/{dealer}/assign', [AdminPeopleController::class, 'assignDealer']);
            Route::post('salesmen/{salesman}/assets', [AdminPeopleController::class, 'assignAsset']);

            Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel']);
            Route::post('orders/{order}/dispatch', [AdminOrderController::class, 'upsertDispatch']);

            Route::post('products', [AdminCatalogController::class, 'storeProduct']);
            Route::post('translations', [AdminCatalogController::class, 'upsertTranslation']);
        });
    });
};

// Versioned endpoints for all new mobile applications.
Route::prefix('v1')->group($registerBawaskarApi);

// Legacy aliases retained so the existing backend/API clients keep working.
$registerBawaskarApi();
