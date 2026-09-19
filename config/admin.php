<?php

use App\Models\Auth\AdminRole;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductHomepageSection;
use App\Models\Catalog\ProductHomepageSectionItem;
use App\Models\Catalog\ProductRelatedProduct;
use App\Models\Catalog\ProductType;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Unit;
use App\Models\Communication\AppTranslation;
use App\Models\Communication\Language;
use App\Models\Communication\Notification;
use App\Models\Communication\SupportTicket;
use App\Models\Communication\WebTranslation;
use App\Models\Courier;
use App\Models\DealerProfile;
use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\SalesmanTarget;
use App\Models\Field\TourPlan;
use App\Models\Finance\Payment;
use App\Models\Hr\AllowanceType;
use App\Models\Hr\Announcement;
use App\Models\Hr\ApprovalWorkflow;
use App\Models\Hr\CommissionRule;
use App\Models\Hr\DeductionType;
use App\Models\Hr\Department;
use App\Models\Hr\Designation;
use App\Models\Hr\EmployeeAllowance;
use App\Models\Hr\EmployeeDeduction;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeSkill;
use App\Models\Hr\Holiday;
use App\Models\Hr\IncentiveRule;
use App\Models\Hr\LeavePolicy;
use App\Models\Hr\PerformanceReview;
use App\Models\Hr\Resignation;
use App\Models\Hr\SalaryAdvance;
use App\Models\Hr\SalaryRevision;
use App\Models\Hr\Shift;
use App\Models\Hr\ShiftAssignment;
use App\Models\Hr\Task;
use App\Models\Hr\TrainingAttendance;
use App\Models\Hr\TrainingProgram;
use App\Models\InternalExpense;
use App\Models\InternalExpenseCategory;
use App\Models\InternalExpenseSubcategory;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\Warehouse;
use App\Models\Location\LgdState;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\ProformaInvoice;
use App\Models\Sales\ReturnRequest;
use App\Models\SalesmanProfile;
use App\Models\Storefront\ContactMessage;
use App\Models\Storefront\DeliveryArea;
use App\Models\Storefront\StorefrontAboutItem;
use App\Models\Storefront\StorefrontFaq;
use App\Models\Storefront\StorefrontFooterLink;
use App\Models\Storefront\StorefrontServiceBlock;
use App\Models\Storefront\StorefrontTeamMember;
use App\Models\Storefront\StorefrontTopbarMessage;
use App\Models\System\AuditLog;
use App\Models\System\Backup;
use App\Models\User;

$active = ['1' => 'Active', '0' => 'Inactive'];
// FAQ categories live in config/storefront.php (the website reads them there); required directly because config files cannot call config() while loading.
$faqCategories = array_map(static fn (array $category): string => $category['label'], (require __DIR__.'/storefront.php')['faq_categories']);
$userStatus = ['active' => 'Active', 'inactive' => 'Inactive', 'pending_approval' => 'Pending Approval'];
$contactStatus = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'];
$orderStatus = ['salesman_review' => 'Salesman Review', 'admin_review' => 'Admin Review', 'approved' => 'Approved', 'packing' => 'Packing', 'dispatched' => 'Dispatched', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
$approvalStatus = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];
$holidayTypes = ['national' => 'National', 'company' => 'Company', 'festival' => 'Festival'];
$weekDays = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'];
$audiences = ['salesman' => 'Salesmen', 'dealer' => 'Dealers', 'customer' => 'Customers', 'all' => 'Everyone'];
$documentTypes = ['aadhaar' => 'Aadhaar', 'pan' => 'PAN', 'driving_license' => 'Driving License', 'bank' => 'Bank Details', 'appointment_letter' => 'Appointment Letter', 'id_card' => 'ID Card', 'certificate' => 'Certificate', 'other' => 'Other'];
$documentStatus = ['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected', 'expired' => 'Expired'];
$advanceStatus = ['pending' => 'Pending', 'approved' => 'Approved', 'disbursed' => 'Disbursed', 'closed' => 'Closed', 'rejected' => 'Rejected'];
$reviewStatus = ['draft' => 'Draft', 'published' => 'Published'];
$allowanceCalculations = AllowanceType::CALCULATIONS;
$deductionCalculations = DeductionType::CALCULATIONS;
$statutoryKinds = DeductionType::STATUTORY_KINDS;
$requestTypes = ApprovalWorkflow::REQUEST_TYPES;
$approverRoles = ApprovalWorkflow::APPROVER_ROLES;
// Blank on an employee allowance/deduction row means "use the type's own rule".
$inheritCalculation = ['' => 'Same as the type'];
$employmentStatuses = SalesmanProfile::EMPLOYMENT_STATUSES;
$resignationStatuses = Resignation::STATUSES;
$settlementStatuses = Resignation::SETTLEMENT_STATUSES;
$incentiveBases = IncentiveRule::BASES;
$incentiveRewards = IncentiveRule::REWARD_TYPES;
$incentiveAudience = IncentiveRule::APPLIES_TO;
$commissionBases = CommissionRule::BASES;
$commissionAudience = CommissionRule::APPLIES_TO;
$taskPriorities = Task::PRIORITIES;
$taskStatuses = Task::STATUSES;
$trainingModes = TrainingProgram::MODES;
$trainingStatuses = TrainingProgram::STATUSES;
$attendanceStatuses = TrainingAttendance::STATUSES;
$skillLevels = EmployeeSkill::LEVELS;
$auditEvents = AuditLog::EVENTS;
$backupStatuses = Backup::STATUSES;

return [
    'brand' => ['name' => 'Bawaskar ERP', 'short_name' => 'BERP'],
    'groups' => [
        ['label' => 'Navigation', 'items' => [
            ['key' => 'dashboard-erp', 'label' => 'Dashboard ERP', 'route' => 'admin.dashboard', 'icon' => 'iconoir-report-columns'],
            ['key' => 'dashboard-hrms', 'label' => 'Dashboard HRMS', 'route' => 'admin.dashboard.hrms', 'icon' => 'iconoir-community'],
        ]],
        ['label' => 'People', 'id' => 'peopleMenu', 'icon' => 'iconoir-community', 'items' => [
            ['key' => 'dealers', 'label' => 'Dealers', 'route' => 'admin.dealers.index', 'icon' => 'iconoir-shop'], ['key' => 'customers', 'label' => 'Customers', 'route' => 'admin.customers.index', 'icon' => 'iconoir-user-love'], ['key' => 'salesmen', 'label' => 'Salesmen', 'route' => 'admin.salesmen.index', 'icon' => 'iconoir-user-badge-check'], ['key' => 'couriers', 'label' => 'Courier', 'route' => 'admin.couriers.index', 'icon' => 'iconoir-delivery-truck'], ]],

        ['label' => 'Sales', 'id' => 'salesMenu', 'icon' => 'iconoir-reports', 'items' => [
            ['key' => 'customer-sales', 'label' => 'Customer', 'id' => 'customerSalesMenu', 'icon' => 'iconoir-user', 'children' => [
                ['key' => 'customer-orders', 'label' => 'Sale Orders', 'route' => 'admin.orders.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-cart'],
                ['key' => 'customer-proforma-invoices', 'label' => 'Proforma Invoices', 'route' => 'admin.proforma-invoices.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-page'],
                ['key' => 'customer-invoices', 'label' => 'Sale Invoices', 'route' => 'admin.invoices.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-receipt'],
                ['key' => 'customer-dispatches', 'label' => 'Dispatch & Delivery', 'route' => 'admin.dispatches.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-delivery-truck'],
                ['key' => 'customer-returns', 'label' => 'Returns & Cancellation', 'route' => 'admin.returns.index', 'params' => ['type' => 'customer'], 'icon' => 'iconoir-undo-action'],
            ]],
            ['key' => 'dealer-sales', 'label' => 'Dealer', 'id' => 'dealerSalesMenu', 'icon' => 'iconoir-shop', 'children' => [
                ['key' => 'dealer-orders', 'label' => 'Sale Orders', 'route' => 'admin.orders.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-cart'],
                ['key' => 'dealer-proforma-invoices', 'label' => 'Proforma Invoices', 'route' => 'admin.proforma-invoices.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-page'],
                ['key' => 'dealer-invoices', 'label' => 'Sale Invoices', 'route' => 'admin.invoices.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-receipt'],
                ['key' => 'dealer-dispatches', 'label' => 'Dispatch & Delivery', 'route' => 'admin.dispatches.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-delivery-truck'],
                ['key' => 'dealer-returns', 'label' => 'Returns & Cancellation', 'route' => 'admin.returns.index', 'params' => ['type' => 'dealer'], 'icon' => 'iconoir-undo-action'],
            ]],
        ]],
        ['label' => 'Products & Inventory', 'id' => 'productInventoryMenu', 'icon' => 'iconoir-box', 'items' => [
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'icon' => 'iconoir-box-iso'],
            ['key' => 'product-related-products', 'label' => 'Related Products', 'route' => 'admin.product-related-products.index', 'icon' => 'iconoir-link'],
            ['key' => 'product-types', 'label' => 'Product Types', 'route' => 'admin.product-types.index', 'icon' => 'iconoir-list'],
            ['key' => 'categories', 'label' => 'Category', 'route' => 'admin.categories.index', 'icon' => 'iconoir-list-select'],
            ['key' => 'brands', 'label' => 'Brand', 'route' => 'admin.brands.index', 'icon' => 'iconoir-medal'],
            ['key' => 'units', 'label' => 'Unit', 'route' => 'admin.units.index', 'icon' => 'iconoir-ruler'],
            ['key' => 'inventory', 'label' => 'Stock', 'route' => 'admin.inventory.index', 'icon' => 'iconoir-package'],
            ['key' => 'warehouses', 'label' => 'Warehouse', 'route' => 'admin.warehouses.index', 'icon' => 'iconoir-home-alt'],
            ['key' => 'homepage-settings', 'label' => 'Homepage Settings', 'route' => 'admin.homepage-settings.index', 'icon' => 'iconoir-www'],
        ]],
        ['label' => 'Finance', 'id' => 'financeMenu', 'icon' => 'iconoir-dollar-circle', 'items' => [
            ['key' => 'payments', 'label' => 'Payments', 'route' => 'admin.payments.index', 'icon' => 'iconoir-credit-card'],
            ['key' => 'collections', 'label' => 'Collections', 'route' => 'admin.collections.index', 'icon' => 'iconoir-wallet'],
            ['key' => 'outstanding', 'label' => 'Outstanding', 'route' => 'admin.outstanding.index', 'icon' => 'iconoir-graph-up'],
        ]],
        ['label' => 'Expense', 'id' => 'companyExpenseMenu', 'icon' => 'iconoir-receive-dollars', 'items' => [
            ['key' => 'internal-expenses', 'label' => 'Expense List', 'route' => 'admin.internal-expenses.index', 'icon' => 'iconoir-notes'], ['key' => 'expense-categories', 'label' => 'Category List', 'route' => 'admin.expense-categories.index', 'icon' => 'iconoir-list-select'], ['key' => 'expense-subcategories', 'label' => 'Subcategory List', 'route' => 'admin.expense-subcategories.index', 'icon' => 'iconoir-list'], ]],
        ['label' => 'HRMS', 'items' => [
            ['key' => 'timesheet', 'label' => 'Timesheet', 'id' => 'timesheetMenu', 'icon' => 'iconoir-calendar', 'children' => [['key' => 'attendance', 'label' => 'Attendance', 'route' => 'admin.attendance.index', 'icon' => 'iconoir-check-circle'], ['key' => 'leaves', 'label' => 'Leave', 'route' => 'admin.leaves.index', 'icon' => 'iconoir-calendar-minus'], ['key' => 'bulk-attendance', 'label' => 'Bulk Attendance', 'route' => 'admin.attendance.bulk', 'icon' => 'iconoir-table-rows']]], ['key' => 'dealer-visits', 'label' => 'Dealer Visits', 'route' => 'admin.dealer-visits.index', 'icon' => 'iconoir-map-pin'], ['key' => 'tour-plans', 'label' => 'Tour Plans', 'route' => 'admin.tour-plans.index', 'icon' => 'iconoir-route'], ['key' => 'expenses', 'label' => 'Expenses', 'route' => 'admin.expenses.index', 'icon' => 'iconoir-receive-dollars'], ['key' => 'salary', 'label' => 'Salary & Payroll', 'route' => 'admin.salary.index', 'icon' => 'iconoir-coins'], ['key' => 'salary-revisions', 'label' => 'Salary Revisions', 'route' => 'admin.salary-revisions.index', 'icon' => 'iconoir-trending-up'], ['key' => 'targets', 'label' => 'Targets & Commission', 'route' => 'admin.targets.index', 'icon' => 'iconoir-target'], ['key' => 'assets', 'label' => 'Salesman Assets', 'route' => 'admin.assets.index', 'icon' => 'iconoir-laptop'],
            ['key' => 'holidays', 'label' => 'Holidays', 'route' => 'admin.holidays.index', 'icon' => 'iconoir-calendar'],
            ['key' => 'shift-menu', 'label' => 'Shifts', 'id' => 'shiftMenu', 'icon' => 'iconoir-clock', 'children' => [['key' => 'shifts', 'label' => 'Shift List', 'route' => 'admin.shifts.index', 'icon' => 'iconoir-clock'], ['key' => 'shift-assignments', 'label' => 'Shift Assignments', 'route' => 'admin.shift-assignments.index', 'icon' => 'iconoir-user']]],
            ['key' => 'announcements', 'label' => 'Announcements', 'route' => 'admin.announcements.index', 'icon' => 'iconoir-megaphone'],
            ['key' => 'employee-documents', 'label' => 'Employee Documents', 'route' => 'admin.employee-documents.index', 'icon' => 'iconoir-page'],
            ['key' => 'salary-advances', 'label' => 'Advances & Loans', 'route' => 'admin.salary-advances.index', 'icon' => 'iconoir-coins'],
            ['key' => 'performance-reviews', 'label' => 'Performance Reviews', 'route' => 'admin.performance-reviews.index', 'icon' => 'iconoir-star'],
            ['key' => 'tasks', 'label' => 'Tasks', 'route' => 'admin.tasks.index', 'icon' => 'iconoir-task-list'],
            ['key' => 'resignations', 'label' => 'Resignation & Exit', 'route' => 'admin.resignations.index', 'icon' => 'iconoir-log-out'],
            ['key' => 'training', 'label' => 'Training', 'id' => 'trainingMenu', 'icon' => 'iconoir-graduation-cap', 'children' => [
                ['key' => 'training-programs', 'label' => 'Training Programs', 'route' => 'admin.training-programs.index', 'icon' => 'iconoir-presentation'],
                ['key' => 'training-attendances', 'label' => 'Training Attendance', 'route' => 'admin.training-attendances.index', 'icon' => 'iconoir-check-circle'],
                ['key' => 'employee-skills', 'label' => 'Skill Records', 'route' => 'admin.employee-skills.index', 'icon' => 'iconoir-medal'],
            ]],
            ['key' => 'incentive-commission', 'label' => 'Incentive & Commission', 'id' => 'incentiveCommissionMenu', 'icon' => 'iconoir-gift', 'children' => [
                ['key' => 'incentive-rules', 'label' => 'Incentive Rules', 'route' => 'admin.incentive-rules.index', 'icon' => 'iconoir-trophy'],
                ['key' => 'commission-rules', 'label' => 'Commission Rules', 'route' => 'admin.commission-rules.index', 'icon' => 'iconoir-percentage'],
            ]],
            ['key' => 'payroll-components', 'label' => 'Allowances & Deductions', 'id' => 'payrollComponentsMenu', 'icon' => 'iconoir-calculator', 'children' => [
                ['key' => 'allowance-types', 'label' => 'Allowance Types', 'route' => 'admin.allowance-types.index', 'icon' => 'iconoir-plus-circle'],
                ['key' => 'deduction-types', 'label' => 'Deduction Types', 'route' => 'admin.deduction-types.index', 'icon' => 'iconoir-minus-circle'],
                ['key' => 'employee-allowances', 'label' => 'Employee Allowances', 'route' => 'admin.employee-allowances.index', 'icon' => 'iconoir-user-plus'],
                ['key' => 'employee-deductions', 'label' => 'Employee Deductions', 'route' => 'admin.employee-deductions.index', 'icon' => 'iconoir-user-xmark'],
            ]],
            ['key' => 'hrms-setup', 'label' => 'HRMS Settings', 'id' => 'hrmsSetupMenu', 'icon' => 'iconoir-settings', 'children' => [
                ['key' => 'hrms-settings', 'label' => 'Attendance & Salary Rules', 'route' => 'admin.hrms-settings.edit', 'icon' => 'iconoir-tools'],
                ['key' => 'departments', 'label' => 'Departments', 'route' => 'admin.departments.index', 'icon' => 'iconoir-building'],
                ['key' => 'designations', 'label' => 'Designations', 'route' => 'admin.designations.index', 'icon' => 'iconoir-user-badge-check'],
                ['key' => 'leave-policies', 'label' => 'Leave Policies', 'route' => 'admin.leave-policies.index', 'icon' => 'iconoir-calendar-minus'],
                ['key' => 'approval-workflows', 'label' => 'Approval Workflow', 'route' => 'admin.approval-workflows.index', 'icon' => 'iconoir-check-circle'],
            ]],
        ]],
        ['label' => 'Storefront', 'id' => 'storefrontMenu', 'icon' => 'iconoir-globe', 'items' => [
            ['key' => 'storefront-footer-links', 'label' => 'Footer Links', 'route' => 'admin.storefront-footer-links.index', 'icon' => 'iconoir-link'], ['key' => 'storefront-service-blocks', 'label' => 'Service Blocks', 'route' => 'admin.storefront-service-blocks.index', 'icon' => 'iconoir-delivery-truck'], ['key' => 'delivery-areas', 'label' => 'Delivery Areas', 'route' => 'admin.delivery-areas.index', 'icon' => 'iconoir-map-pin'], ['key' => 'storefront-faqs', 'label' => 'FAQs', 'route' => 'admin.storefront-faqs.index', 'icon' => 'iconoir-help-circle'], ['key' => 'storefront-about', 'label' => 'About Page', 'route' => 'admin.storefront-about.edit', 'icon' => 'iconoir-info-empty'], ['key' => 'storefront-about-items', 'label' => 'About Sections', 'route' => 'admin.storefront-about-items.index', 'icon' => 'iconoir-list'], ['key' => 'storefront-team-members', 'label' => 'Team Members', 'route' => 'admin.storefront-team-members.index', 'icon' => 'iconoir-group'], ['key' => 'contact-messages', 'label' => 'Contact Messages', 'route' => 'admin.contact-messages.index', 'icon' => 'iconoir-mail-open'], ]],
        ['label' => 'Reports', 'id' => 'reportsMenu', 'icon' => 'iconoir-stats-report', 'items' => []],
        ['label' => 'Translation', 'id' => 'translationMenu', 'icon' => 'iconoir-translate', 'items' => [
            ['key' => 'languages', 'label' => 'Languages', 'route' => 'admin.languages.index', 'icon' => 'iconoir-language'], ['key' => 'app-languages', 'label' => 'App Languages', 'route' => 'admin.app-languages.edit', 'icon' => 'iconoir-smartphone-device'], ['key' => 'translations', 'label' => 'App Translations', 'route' => 'admin.translations.index', 'icon' => 'iconoir-language'], ['key' => 'web-translations', 'label' => 'Website Translations', 'route' => 'admin.web-translations.index', 'icon' => 'iconoir-translate'],
        ]],
        ['label' => 'Settings', 'id' => 'systemMenu', 'icon' => 'iconoir-settings', 'items' => [
            ['key' => 'company-settings', 'label' => 'Company Profile', 'route' => 'admin.company-settings.edit', 'icon' => 'iconoir-building'], ['key' => 'storefront-topbar-messages', 'label' => 'Top Bar Messages', 'route' => 'admin.storefront-topbar-messages.index', 'icon' => 'iconoir-megaphone'], ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'admin.notifications.index', 'icon' => 'iconoir-bell'], ['key' => 'email-templates', 'label' => 'Email Templates', 'route' => 'admin.email-templates.index', 'icon' => 'iconoir-mail'], ['key' => 'support', 'label' => 'Support', 'route' => 'admin.support.index', 'icon' => 'iconoir-headset-help'],
            ['key' => 'users-menu', 'label' => 'Users', 'id' => 'usersMenu', 'icon' => 'iconoir-group', 'children' => [['key' => 'admin-users', 'label' => 'Admin Users', 'route' => 'admin.admin-users.index', 'icon' => 'iconoir-user'], ['key' => 'admin-roles', 'label' => 'Roles & Permissions', 'route' => 'admin.admin-roles.index', 'icon' => 'iconoir-lock']]],
            ['key' => 'audit-logs', 'label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'icon' => 'iconoir-eye'],
            ['key' => 'backups', 'label' => 'Backup & Restore', 'route' => 'admin.backups.index', 'icon' => 'iconoir-database-backup'],
        ]],
    ],
    'modules' => [
        'salesmen' => [
            'label' => 'Salesmen', 'group' => 'People', 'location_required' => false, 'filters' => [['name' => 'state_code', 'label' => 'State', 'column' => 'state_code', 'option_model' => LgdState::class, 'option_value' => 'state_code', 'option_label' => 'name']], 'where' => ['role' => User::ROLE_SALESMAN], 'description' => 'Company field-sales employees with email and password login.', 'model' => User::class, 'with' => ['salesmanProfile.designation'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => $userStatus,
            'columns' => [['key' => 'salesmanProfile.employee_code', 'label' => 'Employee Code'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email', 'type' => 'email'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'salesmanProfile.designation.name', 'label' => 'Designation'], ['key' => 'salesmanProfile.employment_status', 'label' => 'Employment'], ['key' => 'salesmanProfile.territory', 'label' => 'Territory'], ['key' => 'salesmanProfile.basic_salary', 'label' => 'Basic Salary', 'type' => 'money'], ['key' => 'district_name', 'label' => 'District'], ['key' => 'city_village', 'label' => 'City / Village'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['required', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['nullable', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => ['required', 'string', 'min:8', 'confirmed']], ['name' => 'employee_code', 'label' => 'Employee Code', 'rules' => ['required', 'string', 'max:50', 'unique:salesman_profiles,employee_code,{id}']], ['name' => 'department_id', 'label' => 'Department', 'type' => 'select', 'option_model' => Department::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:departments,id']], ['name' => 'designation_id', 'label' => 'Designation', 'type' => 'select', 'option_model' => Designation::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:designations,id']], ['name' => 'reporting_to', 'label' => 'Reporting Manager', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => User::ROLE_SALESMAN], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'confirmation_date', 'label' => 'Confirmation Date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'employment_status', 'label' => 'Employment Status', 'type' => 'select', 'options' => $employmentStatuses, 'default' => 'active', 'rules' => ['required', 'in:'.implode(',', array_keys($employmentStatuses))]], ['name' => 'exit_date', 'label' => 'Exit Date', 'type' => 'date', 'rules' => ['nullable', 'date'], 'help' => 'Filled automatically when a resignation is completed.'], ['name' => 'territory', 'label' => 'Territory', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'basic_salary', 'label' => 'Basic Salary', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'target_amount', 'label' => 'Monthly Target', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'min:0']], ['type' => 'location_picker', 'name' => 'location', 'label' => 'Location'], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $userStatus, 'rules' => ['required', 'in:active,inactive']]],
        ],
        'dealers' => [
            'label' => 'Dealers', 'group' => 'People', 'filters' => [['name' => 'state_code', 'label' => 'State', 'column' => 'state_code', 'option_model' => LgdState::class, 'option_value' => 'state_code', 'option_label' => 'name']], 'where' => ['role' => User::ROLE_DEALER], 'description' => 'B2B dealer registration, approval, salesman assignment, credit and outstanding.', 'model' => User::class, 'with' => ['dealerProfile.salesman'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => $userStatus,
            'columns' => [['key' => 'dealerProfile.dealer_code', 'label' => 'Dealer Code'], ['key' => 'dealerProfile.firm_name', 'label' => 'Firm'], ['key' => 'name', 'label' => 'Contact Person'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'email', 'label' => 'Email', 'type' => 'email'], ['key' => 'dealerProfile.salesman.name', 'label' => 'Salesman'], ['key' => 'dealerProfile.credit_limit', 'label' => 'Credit Limit', 'type' => 'money'], ['key' => 'dealerProfile.outstanding_balance', 'label' => 'Outstanding', 'type' => 'money'], ['key' => 'district_name', 'label' => 'District'], ['key' => 'city_village', 'label' => 'City / Village'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'name', 'label' => 'Contact Person', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['required', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => ['nullable', 'string', 'min:8', 'confirmed']], ['name' => 'dealer_code', 'label' => 'Dealer Code', 'rules' => ['required', 'string', 'max:50', 'unique:dealer_profiles,dealer_code,{id}']], ['name' => 'firm_name', 'label' => 'Firm Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'gst_number', 'label' => 'GST Number', 'rules' => ['nullable', 'string', 'max:30']], ['name' => 'salesman_id', 'label' => 'Assigned Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman', 'status' => 'active'], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'outstanding_balance', 'label' => 'Outstanding Balance', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['type' => 'location_picker', 'name' => 'location', 'label' => 'Location'], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $userStatus, 'rules' => ['required', 'in:active,inactive,pending_approval']]],
        ],
        'customers' => [
            'label' => 'Customers', 'group' => 'People', 'filters' => [['name' => 'state_code', 'label' => 'State', 'column' => 'state_code', 'option_model' => LgdState::class, 'option_value' => 'state_code', 'option_label' => 'name']], 'where' => ['role' => User::ROLE_CUSTOMER], 'description' => 'B2C retail customer accounts and language preferences.', 'model' => User::class, 'with' => ['customerProfile'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => $userStatus,
            'columns' => [['key' => 'name', 'label' => 'Name'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'email', 'label' => 'Email', 'type' => 'email'], ['key' => 'customerProfile.preferred_language', 'label' => 'Language'], ['key' => 'created_at', 'label' => 'Registered', 'type' => 'date'], ['key' => 'district_name', 'label' => 'District'], ['key' => 'city_village', 'label' => 'City / Village'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['required', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => ['nullable', 'string', 'min:8', 'confirmed']], ['name' => 'date_of_birth', 'label' => 'Date of Birth', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'preferred_language', 'label' => 'Preferred Language', 'type' => 'select', 'option_model' => Language::class, 'option_where' => ['is_active' => 1], 'option_value' => 'code', 'option_label' => 'name', 'rules' => ['required', 'string', 'max:10']], ['type' => 'location_picker', 'name' => 'location', 'label' => 'Location'], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $userStatus, 'rules' => ['required', 'in:active,inactive']]],
        ],
        'couriers' => [
            'label' => 'Courier', 'group' => 'People', 'singular' => 'Courier Person', 'description' => 'Courier and delivery person master details.', 'model' => Courier::class, 'search' => ['courier_code', 'name', 'mobile', 'company_name', 'vehicle_number'], 'status_column' => 'status', 'status_options' => ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'],
            'columns' => [['key' => 'courier_code', 'label' => 'Courier Code'], ['key' => 'name', 'label' => 'Name'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'company_name', 'label' => 'Company'], ['key' => 'vehicle_type', 'label' => 'Vehicle'], ['key' => 'vehicle_number', 'label' => 'Vehicle No.'], ['key' => 'service_area', 'label' => 'Service Area'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'courier_code', 'label' => 'Courier Code', 'rules' => ['nullable', 'string', 'max:50', 'unique:couriers,courier_code,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['required', 'string', 'max:20', 'unique:couriers,mobile,{id}']], ['name' => 'alternate_mobile', 'label' => 'Alternate Mobile', 'rules' => ['nullable', 'string', 'max:20']], ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255', 'unique:couriers,email,{id}']], ['name' => 'company_name', 'label' => 'Courier Company', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'vehicle_type', 'label' => 'Vehicle Type', 'type' => 'select', 'options' => ['bike' => 'Bike', 'scooter' => 'Scooter', 'car' => 'Car', 'van' => 'Van', 'tempo' => 'Tempo', 'truck' => 'Truck', 'other' => 'Other'], 'rules' => ['nullable', 'string', 'max:40']], ['name' => 'vehicle_number', 'label' => 'Vehicle Number', 'rules' => ['nullable', 'string', 'max:50']], ['name' => 'license_number', 'label' => 'License Number', 'rules' => ['nullable', 'string', 'max:80']], ['name' => 'service_area', 'label' => 'Service Area', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'address', 'label' => 'Address', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string']], ['name' => 'city', 'label' => 'City', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'pincode', 'label' => 'Pincode', 'rules' => ['nullable', 'string', 'max:20']], ['name' => 'id_proof_type', 'label' => 'ID Proof Type', 'type' => 'select', 'options' => ['aadhaar' => 'Aadhaar', 'pan' => 'PAN', 'driving_license' => 'Driving License', 'voter_id' => 'Voter ID', 'other' => 'Other'], 'rules' => ['nullable', 'string', 'max:40']], ['name' => 'id_proof_number', 'label' => 'ID Proof Number', 'rules' => ['nullable', 'string', 'max:80']], ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'], 'rules' => ['required', 'in:active,inactive,on_leave']], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'categories' => [
            'label' => 'Categories', 'group' => 'Catalog', 'model' => Category::class, 'search' => ['name', 'slug', 'homepage_title'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'image_path', 'label' => 'Image', 'type' => 'image'], ['key' => 'name', 'label' => 'Name'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'show_on_homepage', 'label' => 'Homepage', 'type' => 'boolean'], ['key' => 'homepage_sort_order', 'label' => 'Home Sort'], ['key' => 'homepage_product_limit', 'label' => 'Limit'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['type' => 'section_heading', 'label' => 'Basic Category'],
                ['name' => 'name', 'label' => 'Category Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'image_path', 'label' => 'Category Image - 130 x 130 px', 'type' => 'image', 'upload_dir' => 'uploads/categories', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['type' => 'section_heading', 'label' => 'Homepage Product Row Settings'],
                ['name' => 'show_on_homepage', 'label' => 'Show this category on Homepage Category Slider', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'homepage_title', 'label' => 'Homepage Title', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Leave blank to use category name.'],
                ['name' => 'homepage_layout', 'label' => 'Homepage Layout', 'type' => 'select', 'options' => ['product_slider' => 'Product Slider', 'product_grid' => 'Product Grid'], 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'homepage_product_limit', 'label' => 'Homepage Product Limit', 'type' => 'number', 'default' => 8, 'rules' => ['nullable', 'integer', 'min:1', 'max:50']],
                ['name' => 'homepage_sort_order', 'label' => 'Homepage Sort Order / Row Number', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
            ],
        ],        'brands' => [
            'label' => 'Brands', 'group' => 'Catalog', 'model' => Brand::class, 'search' => ['name'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Brand'], ['key' => 'products_count', 'label' => 'Products'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean'], ['key' => 'created_at', 'label' => 'Created', 'type' => 'date']],
            'with_count' => ['products'], 'fields' => [['name' => 'name', 'label' => 'Brand Name', 'rules' => ['required', 'string', 'max:255', 'unique:brands,name,{id}']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'units' => [
            'label' => 'Units', 'group' => 'Catalog', 'singular' => 'Unit', 'model' => Unit::class, 'search' => ['name', 'short_name', 'unit_type'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Unit Name'], ['key' => 'short_name', 'label' => 'Short Name'], ['key' => 'unit_type', 'label' => 'Type'], ['key' => 'decimal_precision', 'label' => 'Decimal'], ['key' => 'products_count', 'label' => 'Products'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'with_count' => ['products'], 'fields' => [['name' => 'name', 'label' => 'Unit Name', 'rules' => ['required', 'string', 'max:255', 'unique:units,name,{id}']], ['name' => 'short_name', 'label' => 'Short Name', 'rules' => ['required', 'string', 'max:30', 'unique:units,short_name,{id}'], 'help' => 'Example: kg, ltr, pcs, pkt.'], ['name' => 'unit_type', 'label' => 'Unit Type', 'type' => 'select', 'options' => ['weight' => 'Weight', 'volume' => 'Volume', 'quantity' => 'Quantity', 'length' => 'Length', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'decimal_precision', 'label' => 'Decimal Precision', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0', 'max:6']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],        'product-types' => [
            'label' => 'Product Types', 'group' => 'Catalog', 'description' => 'Manage product types used in product master.', 'model' => ProductType::class, 'search' => ['name', 'slug'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Product Type'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'name', 'label' => 'Product Type Name', 'rules' => ['required', 'string', 'max:255']],

                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'products' => [
            'label' => 'Products', 'group' => 'Catalog', 'description' => 'Add product, required size/pack variants, stock, gallery and videos from this single form.', 'model' => Product::class, 'with' => ['category', 'brand', 'homepageSection', 'productType', 'unit', 'images', 'media', 'translations', 'variants.inventoryBatches', 'variants.unit', 'relatedProductLinks.relatedProduct'], 'search' => ['name', 'sku', 'hsn_code'], 'status_column' => 'is_active', 'status_options' => $active, 'form_layout' => 'tabs',
            'filters' => [
                ['name' => 'homepage_section_id', 'label' => 'Section Title', 'column' => 'homepage_section_id', 'option_model' => ProductHomepageSection::class, 'option_label' => 'title'],
            ],
            'columns' => [['key' => 'images.0.path', 'label' => 'Image', 'type' => 'image'], ['key' => 'sku', 'label' => 'SKU'], ['key' => 'name', 'label' => 'Product Name'], ['key' => 'productType.name', 'label' => 'Product Type'], ['key' => 'category.name', 'label' => 'Category'], ['key' => 'brand.name', 'label' => 'Brand'], ['key' => 'homepageSection.title', 'label' => 'Section Title'], ['key' => 'unit.short_name', 'label' => 'Unit'], ['key' => 'dealer_price', 'label' => 'Dealer Price', 'type' => 'money'], ['key' => 'customer_price', 'label' => 'Customer Price', 'type' => 'money']],
            'fields' => [
                ['type' => 'section_heading', 'label' => '1. Basic Information'],
                ['name' => 'name', 'label' => 'Product Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => Category::class, 'rules' => ['required', 'exists:categories,id']],
                ['name' => 'homepage_section_id', 'label' => 'Homepage Section Title', 'type' => 'select', 'option_model' => ProductHomepageSection::class, 'option_where' => ['is_active' => true], 'option_label' => 'title', 'option_attributes' => ['section_type' => 'section_type', 'layout_type' => 'layout_type'], 'rules' => ['nullable', 'exists:product_homepage_sections,id'], 'help' => 'Select any Homepage Settings section. Product will display according to selected section type.'],
                ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'option_model' => Brand::class, 'rules' => ['nullable', 'exists:brands,id']],
                ['name' => 'product_type_id', 'label' => 'Product Type', 'type' => 'select', 'option_model' => ProductType::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:product_types,id']],
                ['name' => 'sort_order', 'label' => 'Product Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0'], 'help' => 'Controls the overall listing order of this product.'],
                ['name' => 'short_description', 'label' => 'Short Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 2, 'maxlength' => 160, 'character_counter' => true, 'rules' => ['nullable', 'string', 'max:160'], 'help' => 'Maximum 160 characters. Product cards show up to 80 characters.'],
                ['name' => 'is_visible_to_dealers', 'label' => 'Visible to Dealers', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Makes this product visible in dealer-facing listings and orders.'],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Keeps this product enabled for use in the system.'],
                ['name' => 'is_visible_to_customers', 'label' => 'Visible to Customers', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Makes this product visible in the public customer storefront.'],
                ['name' => 'show_on_homepage', 'label' => 'Allow product on homepage product rows', 'type' => 'checkbox', 'default' => 1, 'rules' => ['boolean'], 'help' => 'Allows this product to appear in homepage product sections.'],

                ['type' => 'section_heading', 'label' => '2. Variant Details (Required)'],
                ['name' => 'variants', 'label' => 'Size / Pack Variants', 'type' => 'product_variants_repeater', 'col' => 'col-12', 'rules' => ['required', 'array', 'min:1'], 'help' => 'At least one active variant and exactly one Main Product are required. Price, tax, unit, SKU, HSN and opening stock are maintained inside each variant.'],

                ['type' => 'section_heading', 'label' => '3. Images & Gallery'],
                ['name' => 'primary_image', 'label' => 'Main Product Image - 500 x 500 px for cards / 750 x 750 px for detail', 'type' => 'image', 'upload_dir' => 'uploads/products', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'gallery_images', 'label' => 'Product Gallery Images - 150 x 150 px thumbnails', 'type' => 'image_multiple', 'upload_dir' => 'uploads/products/gallery', 'rules' => ['nullable', 'array']],

                ['type' => 'section_heading', 'label' => '4. Product Videos'],
                ['name' => 'media', 'label' => 'Gallery Videos', 'type' => 'product_media_repeater', 'col' => 'col-12', 'rules' => ['nullable', 'array'], 'help' => 'Add multiple uploaded MP4/WebM videos or YouTube URLs. Videos appear in the same product gallery.'],

                ['type' => 'section_heading', 'label' => '5. Deal Timer / Stock Display'],
                ['name' => 'sale_badge_text', 'label' => 'Sale Badge Text', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'sold_quantity', 'label' => 'Sold Quantity', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'total_quantity', 'label' => 'Total Quantity', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'low_stock_text', 'label' => 'Low Stock Text', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'offer_start_at', 'label' => 'Offer Start Date & Time', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
                ['name' => 'offer_end_at', 'label' => 'Offer End Date & Time', 'type' => 'datetime-local', 'rules' => ['nullable', 'date', 'after_or_equal:offer_start_at']],
                ['name' => 'is_offer_active', 'label' => 'Offer Timer Active', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Turns the countdown offer timer on for this product.'],

                ['type' => 'section_heading', 'label' => '6. Product Language Translations'],
                ['type' => 'product_translation_tools', 'label' => 'Auto Translate from Product Name & Description'],
                ['name' => 'translation_hi_name', 'label' => 'Hindi Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Hindi product name'],
                ['name' => 'translation_hi_description', 'label' => 'Hindi Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Hindi description'],
                ['name' => 'translation_mr_name', 'label' => 'Marathi Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Marathi product name'],
                ['name' => 'translation_mr_description', 'label' => 'Marathi Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Marathi description'],
                ['name' => 'translation_gu_name', 'label' => 'Gujarati Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Gujarati product name'],
                ['name' => 'translation_gu_description', 'label' => 'Gujarati Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Gujarati description'],
                ['name' => 'translation_kn_name', 'label' => 'Kannada Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Kannada product name'],
                ['name' => 'translation_kn_description', 'label' => 'Kannada Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Kannada description'],
                ['name' => 'translation_te_name', 'label' => 'Telugu Product Name', 'rules' => ['nullable', 'string', 'max:255'], 'placeholder' => 'Auto translate or enter Telugu product name'],
                ['name' => 'translation_te_description', 'label' => 'Telugu Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'placeholder' => 'Auto translate or enter Telugu description'],

                ['type' => 'section_heading', 'label' => '7. SEO', 'display_only' => true],                ['name' => 'meta_title', 'label' => 'Meta Title', 'rules' => ['nullable', 'string', 'max:255'], 'display_only' => true],
                ['name' => 'meta_description', 'label' => 'Meta Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'display_only' => true],
                ['name' => 'meta_keywords', 'label' => 'Meta Keywords', 'rules' => ['nullable', 'string', 'max:255'], 'display_only' => true],

                ['type' => 'section_heading', 'label' => '8. Bottom Details'],
                ['type' => 'product_bottom_details', 'label' => 'Bottom Details', 'groups' => [
                    'description' => 'Description',
                    'additional_information' => 'Additional Information',
                    'care_instructions' => 'Care Instructions',
                ]],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-md-6', 'rows' => 7, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'benefits', 'label' => 'Benefits', 'type' => 'textarea', 'col' => 'col-md-6', 'rows' => 7, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'usage_instructions', 'label' => 'Usage Instructions', 'type' => 'textarea', 'col' => 'col-md-6', 'rows' => 7, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'crop_information', 'label' => 'Crop Information', 'type' => 'textarea', 'col' => 'col-md-6', 'rows' => 7, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'detail_banner_image', 'label' => 'Detail Page Description Banner - 1199 x 97 px', 'type' => 'image', 'upload_dir' => 'uploads/products/detail-banners', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'detail_banner_url', 'label' => 'Detail Page Description Banner Link', 'rules' => ['nullable', 'string', 'max:255'], 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'detail_banner_position', 'label' => 'Description Banner Position', 'type' => 'radio', 'col' => 'col-12', 'options' => ['before' => 'Start of description', 'middle' => 'Middle of description', 'after' => 'End of description'], 'rules' => ['required', 'in:before,middle,after'], 'default' => 'after', 'help' => 'Decides where the banner appears inside the Description tab on the product detail page.', 'render_inside' => 'product_bottom_details', 'render_group' => 'description'],
                ['name' => 'additional_info', 'label' => 'Additional Information', 'type' => 'product_additional_information_repeater', 'rules' => ['nullable', 'array'], 'render_inside' => 'product_bottom_details', 'render_group' => 'additional_information'],
                ['name' => 'care_instructions', 'label' => 'Care Instructions', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'render_inside' => 'product_bottom_details', 'render_group' => 'care_instructions'],

                ['type' => 'section_heading', 'label' => '9. Homepage Display Fields', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'product_section', 'coupon_section', 'top_selling_section', 'offer_section', 'strip_offer_banner', 'service_section', 'blog_section']],
                ['name' => 'homepage_title', 'label' => 'Homepage Title', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'coupon_section', 'strip_offer_banner', 'blog_section', 'service_section']],
                ['name' => 'homepage_subtitle', 'label' => 'Homepage Subtitle', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'blog_section', 'service_section']],
                ['name' => 'homepage_description', 'label' => 'Homepage Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'blog_section']],
                ['name' => 'homepage_image_path', 'label' => 'Homepage Image / Banner Image', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'help' => 'Hero: 1920x637, Product card: 500x500, Small banner: 375x243, Offer banner depends on layout.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'offer_section', 'strip_offer_banner', 'blog_section']],
                ['name' => 'homepage_mobile_image_path', 'label' => 'Homepage Mobile Image', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage/mobile', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'offer_section', 'strip_offer_banner', 'blog_section']],
                ['name' => 'homepage_logo_image_path', 'label' => 'Homepage Logo Image / Coupon Logo', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage/logos', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'help' => 'Coupon logo recommended 290x90.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_offer_image_path', 'label' => 'Homepage Offer Image', 'type' => 'image', 'upload_dir' => 'uploads/products/homepage/offers', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'help' => 'Coupon offer image recommended 250x200.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_highlight_text', 'label' => 'Homepage Highlight Text', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['strip_offer_banner']],
                ['name' => 'homepage_discount_text', 'label' => 'Homepage Discount Text', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section', 'strip_offer_banner']],
                ['name' => 'homepage_validity_text', 'label' => 'Homepage Validity Text', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_coupon_code', 'label' => 'Homepage Coupon Code', 'rules' => ['nullable', 'string', 'max:80'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['coupon_section']],
                ['name' => 'homepage_button_text', 'label' => 'Homepage Button Text', 'rules' => ['nullable', 'string', 'max:120'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'strip_offer_banner']],
                ['name' => 'homepage_button_url', 'label' => 'Homepage Button Link', 'rules' => ['nullable', 'string', 'max:255'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'offer_section', 'strip_offer_banner', 'blog_section']],
                ['name' => 'homepage_icon_key', 'label' => 'Homepage Service Icon / SVG Key', 'rules' => ['nullable', 'string', 'max:120'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['service_section']],
                ['name' => 'homepage_slot', 'label' => 'Homepage Slot / Position', 'rules' => ['nullable', 'string', 'max:80'], 'help' => 'Example: big, small, left, right, first, second.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['top_small_banners', 'offer_section']],
                ['name' => 'homepage_background_color', 'label' => 'Homepage Background Color', 'rules' => ['nullable', 'string', 'max:30'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['strip_offer_banner']],
                ['name' => 'homepage_text_color', 'label' => 'Homepage Text Color', 'rules' => ['nullable', 'string', 'max:30'], 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['strip_offer_banner']],
                ['name' => 'homepage_sort_order', 'label' => 'Homepage Section Product Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0'], 'help' => 'Controls only this product position inside the selected homepage section. It does not change the normal catalog listing order.', 'visibility_field' => 'homepage_section_id', 'show_for_section_types' => ['hero_slider', 'top_small_banners', 'product_section', 'coupon_section', 'top_selling_section', 'offer_section', 'strip_offer_banner', 'service_section', 'blog_section']],

                ['type' => 'section_heading', 'label' => '10. Homepage / Display Flags'],
                ['name' => 'is_featured', 'label' => 'Featured Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Marks this product as featured for highlighted listings.'],
                ['name' => 'is_top_selling', 'label' => 'Top Selling Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Uses this product in top-selling product collections.'],
                ['name' => 'is_trending', 'label' => 'Trending Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Marks this product for trending product sections.'],
                ['name' => 'is_new_arrival', 'label' => 'New Arrival Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Shows this product in new-arrival selections.'],
                ['name' => 'is_offer_product', 'label' => 'Offer Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Includes this product in offer-based product groups.'],
                ['name' => 'is_deal_timer_product', 'label' => 'Deal Timer Product', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Uses this product as the special offer card in Top Selling Items.'],
            ],
        ],        'homepage-settings' => [
            'label' => 'Homepage Settings',
            'sort' => ['sort_order', 'asc'],
            'group' => 'Catalog',
            'description' => 'Create homepage rows/design only. Actual product/banner/text/offer content is managed from Products.',
            'model' => ProductHomepageSection::class,
            'with' => ['category'],
            'search' => ['title', 'section_type', 'layout_type'],
            'status_column' => 'is_active',
            'status_options' => $active,
            'columns' => [
                ['key' => 'title', 'label' => 'Section Title'],
                ['key' => 'section_type_name', 'label' => 'Section Type'],
                ['key' => 'layout_type_name', 'label' => 'Layout Type'],
                ['key' => 'category.name', 'label' => 'Category'],
                ['key' => 'product_limit', 'label' => 'Item Limit'],
                ['key' => 'sort_order', 'label' => 'Sort Order'],
                ['key' => 'is_active', 'label' => 'Active', 'type' => 'boolean'],
            ],
            'fields' => [
                ['type' => 'section_heading', 'label' => 'Homepage Row Settings'],

                ['name' => 'title', 'label' => 'Section Title', 'rules' => ['required', 'string', 'max:255']],

                ['name' => 'section_type', 'label' => 'Section Type', 'type' => 'select', 'options' => [
                    'hero_slider' => 'Hero Slider',
                    'top_small_banners' => 'Top Small Banners',
                    'category_section' => 'Category Section',
                    'product_section' => 'Product Section',
                    'coupon_section' => 'Coupon Section',
                    'top_selling_section' => 'Top Selling Section',
                    'offer_section' => 'Offer Section',
                    'strip_offer_banner' => 'Strip Offer Banner',
                    'service_section' => 'Service Section',
                    'blog_section' => 'Blog Section',
                    'video_section' => 'Video Section',
                ], 'rules' => ['required', 'string', 'max:80']],

                ['name' => 'layout_type', 'label' => 'Layout Type', 'type' => 'select', 'options' => [
                    'full_width_slider' => 'Full Width Slider',
                    'four_banner_slider' => 'Four Banner Slider',
                    'category_slider' => 'Category Slider',
                    'product_slider' => 'Product Slider',
                    'product_grid' => 'Product Grid',
                    'coupon_slider' => 'Coupon Slider',
                    'products_with_offer' => 'Products With Special Offer',
                    'two_column_banner' => 'Two Column Banner',
                    'big_small_banner' => 'Big Small Banner',
                    'full_width_banner' => 'Full Width Banner',
                    'text_strip' => 'Text Strip',
                    'service_icons' => 'Service Icons',
                    'blog_slider' => 'Blog Slider',
                    'two_videos' => 'Two Videos',
                    'video_text' => 'Video + Text',
                ], 'rules' => ['nullable', 'string', 'max:80']],

                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => Category::class, 'rules' => ['nullable', 'exists:categories,id'], 'help' => 'Use this only when Section Type is Product Section.'],

                ['name' => 'product_limit', 'label' => 'Item Limit', 'type' => 'number', 'default' => 8, 'rules' => ['nullable', 'integer', 'min:1', 'max:50']],

                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],

                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'homepage-setting-items' => [
            'label' => 'Homepage Setting Items',
            'group' => 'Catalog',
            'description' => 'Banners, coupon cards, strip text and service blocks used inside Homepage Settings.',
            'model' => ProductHomepageSectionItem::class,
            'with' => ['section'],
            'search' => ['title', 'subtitle', 'coupon_code', 'slot'],
            'status_column' => 'is_active',
            'status_options' => $active,
            'columns' => [
                ['key' => 'section.title', 'label' => 'Homepage Section'],
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'slot', 'label' => 'Slot'],
                ['key' => 'coupon_code', 'label' => 'Coupon Code'],
                ['key' => 'sort_order', 'label' => 'Sort Order'],
                ['key' => 'is_active', 'label' => 'Active', 'type' => 'boolean'],
            ],
            'fields' => [
                ['name' => 'section_id', 'label' => 'Homepage Section', 'type' => 'select', 'option_model' => ProductHomepageSection::class, 'option_label' => 'title', 'rules' => ['required', 'exists:product_homepage_sections,id']],
                ['name' => 'title', 'label' => 'Title', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'subtitle', 'label' => 'Subtitle', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 3, 'rules' => ['nullable', 'string']],
                ['name' => 'highlight_text', 'label' => 'Highlight Text', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'image_path', 'label' => 'Image', 'type' => 'image', 'upload_dir' => 'uploads/homepage', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'mobile_image_path', 'label' => 'Mobile Image', 'type' => 'image', 'upload_dir' => 'uploads/homepage/mobile', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'logo_image_path', 'label' => 'Coupon / Bank Logo', 'type' => 'image', 'upload_dir' => 'uploads/homepage/logos', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048']],
                ['name' => 'offer_image_path', 'label' => 'Offer Image', 'type' => 'image', 'upload_dir' => 'uploads/homepage/offers', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'video_url', 'label' => 'Video Link', 'col' => 'col-12', 'help' => 'Video Section only. YouTube, Vimeo or a direct .mp4 link. A link is used even if a file is uploaded below.', 'rules' => ['nullable', 'string', 'max:2048']],
                ['name' => 'video_file_path', 'label' => 'Video File (MP4)', 'type' => 'file', 'accept' => 'video/mp4,video/webm', 'upload_dir' => 'uploads/storefront/videos', 'help' => 'Use only when you have no link. Keep it small - large files slow the homepage down.', 'rules' => ['nullable', 'mimetypes:video/mp4,video/webm', 'max:51200']],
                ['name' => 'video_autoplay', 'label' => 'Autoplay Video (muted)', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'button_text', 'label' => 'Button Text', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'button_url', 'label' => 'Button Link', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'coupon_code', 'label' => 'Coupon Code', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'discount_text', 'label' => 'Discount Text', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'validity_text', 'label' => 'Validity Text', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'icon_key', 'label' => 'Service Icon / SVG Key', 'rules' => ['nullable', 'string', 'max:120']],
                ['name' => 'background_color', 'label' => 'Background Color', 'rules' => ['nullable', 'string', 'max:30']],
                ['name' => 'text_color', 'label' => 'Text Color', 'rules' => ['nullable', 'string', 'max:30']],
                ['name' => 'slot', 'label' => 'Slot / Position', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'product-related-products' => [
            'label' => 'Related Products', 'group' => 'Catalog', 'singular' => 'Related Product', 'model' => ProductRelatedProduct::class, 'with' => ['product', 'relatedProduct'], 'search' => [],
            'columns' => [['key' => 'product.name', 'label' => 'Product'], ['key' => 'relatedProduct.name', 'label' => 'Related Product'], ['key' => 'sort_order', 'label' => 'Sort']],
            'fields' => [
                ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'option_model' => Product::class, 'option_label' => 'name', 'rules' => ['required', 'exists:products,id']],
                ['name' => 'related_product_id', 'label' => 'Related Product', 'type' => 'select', 'option_model' => Product::class, 'option_label' => 'name', 'rules' => ['required', 'exists:products,id', 'different:product_id']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
            ],
        ],
        'pricing' => [
            'label' => 'Dealer & Customer Pricing', 'group' => 'Catalog', 'singular' => 'Product Price', 'model' => Product::class, 'with' => ['category'], 'search' => ['name', 'sku'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'sku', 'label' => 'SKU'], ['key' => 'name', 'label' => 'Product'], ['key' => 'mrp', 'label' => 'MRP', 'type' => 'money'], ['key' => 'dealer_price', 'label' => 'Dealer Price', 'type' => 'money'], ['key' => 'customer_price', 'label' => 'Customer Price', 'type' => 'money'], ['key' => 'gst_percent', 'label' => 'GST %']],
            'fields' => [['name' => 'mrp', 'label' => 'MRP', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'dealer_price', 'label' => 'Dealer Price', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'customer_price', 'label' => 'Customer Price', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'gst_percent', 'label' => 'GST %', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0', 'max:100'], 'help' => 'Enter GST percent. Use 0 if GST is not applicable.']],
        ],
        'warehouses' => [
            'label' => 'Warehouses', 'group' => 'Inventory', 'model' => Warehouse::class, 'search' => ['name', 'code', 'city'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'code', 'label' => 'Code'], ['key' => 'name', 'label' => 'Warehouse'], ['key' => 'city', 'label' => 'City'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'name', 'label' => 'Warehouse Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'code', 'label' => 'Code', 'rules' => ['required', 'string', 'max:50', 'unique:warehouses,code,{id}']], ['name' => 'city', 'label' => 'City', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],

        'inventory' => [
            'label' => 'Stock', 'group' => 'Inventory', 'singular' => 'Stock Record', 'model' => InventoryBatch::class, 'with' => ['product', 'variant', 'warehouse'], 'search' => ['batch_no'], 'columns' => [['key' => 'product.name', 'label' => 'Product'], ['key' => 'variant.value', 'label' => 'Size / Pack'], ['key' => 'warehouse.name', 'label' => 'Warehouse'], ['key' => 'batch_no', 'label' => 'Batch'], ['key' => 'quantity', 'label' => 'Retail Pack Quantity'], ['key' => 'reserved_quantity', 'label' => 'Reserved'], ['key' => 'low_stock_alert', 'label' => 'Low Stock Level'], ['key' => 'expiry_date', 'label' => 'Expiry', 'type' => 'date']],
            'fields' => [
                ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'option_model' => Product::class, 'option_label' => 'name', 'rules' => ['required', 'exists:products,id']],
                ['name' => 'product_variant_id', 'label' => 'Size / Pack Variant', 'type' => 'select', 'option_model' => ProductVariant::class, 'option_label' => 'value', 'rules' => ['nullable', 'exists:product_variants,id'], 'help' => 'Select a variant when this product has Size / Pack variants. Quantity is entered in retail packs/bottles.'],
                ['name' => 'warehouse_id', 'label' => 'Warehouse', 'type' => 'select', 'option_model' => Warehouse::class, 'option_label' => 'name', 'rules' => ['required', 'exists:warehouses,id']],
                ['name' => 'batch_no', 'label' => 'Batch Number', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'manufacturing_date', 'label' => 'Manufacturing Date', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'expiry_date', 'label' => 'Expiry Date', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:manufacturing_date']],
                ['name' => 'purchase_price', 'label' => 'Purchase Price', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'quantity', 'label' => 'Current Quantity', 'type' => 'number', 'step' => '0.001', 'rules' => ['required', 'numeric', 'min:0']],
                ['name' => 'reserved_quantity', 'label' => 'Reserved Quantity', 'type' => 'number', 'step' => '0.001', 'default' => 0, 'rules' => ['required', 'numeric', 'min:0', 'lte:quantity']],
                ['name' => 'low_stock_alert', 'label' => 'Low Stock Alert', 'type' => 'number', 'step' => '0.001', 'default' => 0, 'rules' => ['required', 'numeric', 'min:0']],
            ],
        ],
        'orders' => [
            'label' => 'Sale Orders', 'group' => 'Sales', 'channel' => ['column' => 'order_type'], 'singular' => 'Sale Order', 'model' => Order::class, 'with' => ['customer', 'dealer.dealerProfile', 'salesman', 'invoice', 'proformaInvoices'], 'search' => ['order_no'], 'status_column' => 'status', 'status_options' => $orderStatus, 'filters' => [['name' => 'type', 'column' => 'order_type']], 'can_delete' => false,
            'columns' => [['key' => 'order_no', 'label' => 'Sale Order No.'], ['key' => 'order_type', 'label' => 'Channel'], ['key' => 'dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'customer.name', 'label' => 'Customer'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'grand_total', 'label' => 'Total', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'created_at', 'label' => 'Date', 'type' => 'date']],
            'fields' => [['name' => 'order_type', 'label' => 'Channel', 'type' => 'select', 'query_key' => 'type', 'options' => ['customer' => 'Customer', 'dealer' => 'Dealer'], 'rules' => ['required', 'in:customer,dealer']], ['name' => 'order_no', 'label' => 'Sale Order Number', 'rules' => ['nullable', 'string', 'max:80', 'unique:orders,order_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'customer'], 'rules' => ['nullable', 'required_if:order_type,customer', 'exists:users,id']], ['name' => 'dealer_id', 'label' => 'Dealer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'dealer'], 'rules' => ['nullable', 'required_if:order_type,dealer', 'exists:users,id']], ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'gst_total', 'label' => 'GST Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'discount_total', 'label' => 'Discount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to calculate from subtotal + GST - discount.'], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'proforma-invoices' => [
            'label' => 'Proforma Invoices', 'group' => 'Sales', 'channel' => ['relation' => 'order', 'column' => 'order_type'], 'singular' => 'Proforma Invoice', 'description' => 'Quotation-style invoice generated before sale invoice and dispatch.', 'model' => ProformaInvoice::class, 'with' => ['order.customer', 'order.dealer.dealerProfile', 'order.salesman'], 'search' => ['proforma_no'], 'status_column' => 'status', 'status_options' => ['draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted', 'converted' => 'Converted', 'cancelled' => 'Cancelled'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']],
            'columns' => [['key' => 'proforma_no', 'label' => 'Proforma No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'order.dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'order.customer.name', 'label' => 'Customer'], ['key' => 'proforma_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date'], ['key' => 'grand_total', 'label' => 'Total', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id']], ['name' => 'proforma_no', 'label' => 'Proforma Number', 'rules' => ['nullable', 'string', 'max:80', 'unique:proforma_invoices,proforma_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'proforma_date', 'label' => 'Proforma Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'valid_until', 'label' => 'Valid Until', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:proforma_date']], ['name' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'gst_total', 'label' => 'GST Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'discount_total', 'label' => 'Discount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted', 'converted' => 'Converted', 'cancelled' => 'Cancelled'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'invoices' => [
            'label' => 'Sale Invoices', 'group' => 'Sales', 'channel' => ['relation' => 'order', 'column' => 'order_type'], 'singular' => 'Sale Invoice', 'model' => Invoice::class, 'with' => ['order.customer', 'order.dealer.dealerProfile'], 'search' => ['invoice_no'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']], 'can_delete' => false,
            'columns' => [['key' => 'invoice_no', 'label' => 'Invoice No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'order.dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'order.customer.name', 'label' => 'Customer'], ['key' => 'invoice_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'grand_total', 'label' => 'Total', 'type' => 'money']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id', 'unique:invoices,order_id,{id}']], ['name' => 'invoice_no', 'label' => 'Invoice Number', 'rules' => ['nullable', 'string', 'max:80', 'unique:invoices,invoice_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'invoice_date', 'label' => 'Invoice Date', 'type' => 'date', 'rules' => ['nullable', 'date'], 'help' => 'Leave blank to use today.'], ['name' => 'grand_total', 'label' => 'Grand Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to copy from sale order.'], ['name' => 'pdf_path', 'label' => 'PDF Path', 'rules' => ['nullable', 'string', 'max:255']]],
        ],
        'dispatches' => [
            'label' => 'Dispatch & Delivery', 'group' => 'Sales', 'channel' => ['relation' => 'order', 'column' => 'order_type'], 'singular' => 'Dispatch', 'model' => Dispatch::class, 'with' => ['order'], 'search' => ['dispatch_no', 'courier_name', 'tracking_no'], 'status_column' => 'status', 'status_options' => ['packing' => 'Packing', 'dispatched' => 'Dispatched', 'in_transit' => 'In Transit', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'returned' => 'Returned'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']],
            'columns' => [['key' => 'dispatch_no', 'label' => 'Dispatch No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'courier_name', 'label' => 'Courier'], ['key' => 'tracking_no', 'label' => 'Tracking'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'dispatched_at', 'label' => 'Dispatched', 'type' => 'datetime']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id']], ['name' => 'dispatch_no', 'label' => 'Dispatch Number', 'rules' => ['required', 'string', 'max:80', 'unique:dispatches,dispatch_no,{id}']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['packing' => 'Packing', 'dispatched' => 'Dispatched', 'in_transit' => 'In Transit', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'returned' => 'Returned'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'courier_name', 'label' => 'Courier Name', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'tracking_no', 'label' => 'Tracking Number', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'tracking_url', 'label' => 'Tracking URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:255']], ['name' => 'dispatched_at', 'label' => 'Dispatched At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']], ['name' => 'out_for_delivery_at', 'label' => 'Out for Delivery At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']], ['name' => 'delivered_at', 'label' => 'Delivered At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']]],
        ],
        'returns' => [
            'label' => 'Returns & Cancellation', 'group' => 'Sales', 'channel' => ['relation' => 'order', 'column' => 'order_type'], 'singular' => 'Return Request', 'model' => ReturnRequest::class, 'with' => ['order', 'user'], 'search' => ['return_no', 'reason'], 'status_column' => 'status', 'status_options' => ['requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'received' => 'Received', 'refunded' => 'Refunded'], 'filters' => [['name' => 'type', 'relation' => 'order', 'column' => 'order_type']],
            'columns' => [['key' => 'return_no', 'label' => 'Return No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'user.name', 'label' => 'Requested By'], ['key' => 'reason', 'label' => 'Reason'], ['key' => 'refund_amount', 'label' => 'Refund', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['required', 'exists:orders,id']], ['name' => 'user_id', 'label' => 'Requested By', 'type' => 'select', 'option_model' => User::class, 'rules' => ['required', 'exists:users,id']], ['name' => 'return_no', 'label' => 'Return Number', 'rules' => ['required', 'string', 'max:80', 'unique:return_requests,return_no,{id}']], ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'rules' => ['required', 'string']], ['name' => 'refund_amount', 'label' => 'Refund Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'received' => 'Received', 'refunded' => 'Refunded'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'payments' => [
            'label' => 'Payments', 'group' => 'Finance', 'model' => Payment::class, 'with' => ['order', 'payer', 'collector'], 'search' => ['payment_no', 'transaction_ref'], 'status_column' => 'status', 'status_options' => ['pending' => 'Pending', 'paid' => 'Paid', 'collected' => 'Collected', 'verified' => 'Verified', 'failed' => 'Failed', 'refunded' => 'Refunded'],
            'columns' => [['key' => 'payment_no', 'label' => 'Payment No.'], ['key' => 'order.order_no', 'label' => 'Sale Order'], ['key' => 'payer.name', 'label' => 'Payer'], ['key' => 'payment_mode', 'label' => 'Mode'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'paid_at', 'label' => 'Paid At', 'type' => 'datetime']],
            'fields' => [['name' => 'order_id', 'label' => 'Sale Order', 'type' => 'select', 'option_model' => Order::class, 'option_label' => 'order_no', 'rules' => ['nullable', 'exists:orders,id']], ['name' => 'payer_id', 'label' => 'Payer', 'type' => 'select', 'option_model' => User::class, 'rules' => ['nullable', 'exists:users,id']], ['name' => 'collected_by', 'label' => 'Collected By', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['nullable', 'exists:users,id']], ['name' => 'payment_no', 'label' => 'Payment Number', 'rules' => ['required', 'string', 'max:80', 'unique:payments,payment_no,{id}']], ['name' => 'payment_mode', 'label' => 'Payment Mode', 'type' => 'select', 'options' => ['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'gateway' => 'Online Gateway'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0.01']], ['name' => 'transaction_ref', 'label' => 'Transaction Reference', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'paid' => 'Paid', 'collected' => 'Collected', 'verified' => 'Verified', 'failed' => 'Failed', 'refunded' => 'Refunded'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'paid_at', 'label' => 'Paid At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']]],
        ],
        'collections' => [
            'label' => 'Collections', 'group' => 'Finance', 'where_not_null' => ['collected_by'], 'singular' => 'Collection', 'model' => Payment::class, 'with' => ['payer', 'collector', 'order'], 'search' => ['payment_no', 'transaction_ref'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'payment_no', 'label' => 'Receipt No.'], ['key' => 'payer.name', 'label' => 'Dealer'], ['key' => 'collector.name', 'label' => 'Collected By'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'payment_mode', 'label' => 'Mode'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'paid_at', 'label' => 'Date', 'type' => 'datetime']],
            'fields' => [['name' => 'status', 'label' => 'Verification Status', 'type' => 'select', 'options' => ['collected' => 'Collected', 'verified' => 'Verified', 'rejected' => 'Rejected'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'transaction_ref', 'label' => 'Reference', 'rules' => ['nullable', 'string', 'max:255']]],
        ],
        'outstanding' => [
            'label' => 'Outstanding', 'group' => 'Finance', 'singular' => 'Dealer Outstanding', 'model' => DealerProfile::class, 'with' => ['user', 'salesman'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'dealer_code', 'label' => 'Dealer Code'], ['key' => 'firm_name', 'label' => 'Firm'], ['key' => 'user.name', 'label' => 'Contact'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'money'], ['key' => 'outstanding_balance', 'label' => 'Outstanding', 'type' => 'money']],
            'fields' => [['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'outstanding_balance', 'label' => 'Outstanding Balance', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']]],
        ],
        'expense-categories' => [
            'label' => 'Expense Categories', 'group' => 'Expense', 'singular' => 'Expense Category', 'model' => InternalExpenseCategory::class, 'with_count' => ['subcategories', 'expenses'], 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Category'], ['key' => 'code', 'label' => 'Code'], ['key' => 'subcategories_count', 'label' => 'Subcategories'], ['key' => 'expenses_count', 'label' => 'Expenses'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'name', 'label' => 'Category Name', 'rules' => ['required', 'string', 'max:255', 'unique:internal_expense_categories,name,{id}']], ['name' => 'code', 'label' => 'Code', 'rules' => ['nullable', 'string', 'max:50', 'unique:internal_expense_categories,code,{id}']], ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'expense-subcategories' => [
            'label' => 'Expense Subcategories', 'group' => 'Expense', 'singular' => 'Expense Subcategory', 'model' => InternalExpenseSubcategory::class, 'with' => ['category'], 'with_count' => ['expenses'], 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'category.name', 'label' => 'Category'], ['key' => 'name', 'label' => 'Subcategory'], ['key' => 'code', 'label' => 'Code'], ['key' => 'expenses_count', 'label' => 'Expenses'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => InternalExpenseCategory::class, 'option_where' => ['is_active' => true], 'rules' => ['required', 'exists:internal_expense_categories,id']], ['name' => 'name', 'label' => 'Subcategory Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'code', 'label' => 'Code', 'rules' => ['nullable', 'string', 'max:50']], ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'internal-expenses' => [
            'label' => 'Internal Expenses', 'group' => 'Expense', 'singular' => 'Internal Expense', 'model' => InternalExpense::class, 'with' => ['category', 'subcategory', 'payer'], 'search' => ['expense_no', 'title', 'vendor_name', 'payment_mode'], 'status_column' => 'status', 'status_options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid', 'cancelled' => 'Cancelled'], 'date_column' => 'expense_date',
            'columns' => [['key' => 'expense_no', 'label' => 'Expense No.'], ['key' => 'expense_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'category.name', 'label' => 'Category'], ['key' => 'subcategory.name', 'label' => 'Subcategory'], ['key' => 'title', 'label' => 'Title'], ['key' => 'vendor_name', 'label' => 'Vendor'], ['key' => 'payment_mode', 'label' => 'Payment Mode'], ['key' => 'total_amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'expense_no', 'label' => 'Expense No.', 'rules' => ['nullable', 'string', 'max:80', 'unique:internal_expenses,expense_no,{id}'], 'help' => 'Leave blank to auto-generate.'], ['name' => 'expense_date', 'label' => 'Expense Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => InternalExpenseCategory::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:internal_expense_categories,id']], ['name' => 'subcategory_id', 'label' => 'Subcategory', 'type' => 'select', 'option_model' => InternalExpenseSubcategory::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:internal_expense_subcategories,id']], ['name' => 'title', 'label' => 'Expense Title', 'rules' => ['required', 'string', 'max:255']], ['name' => 'vendor_name', 'label' => 'Vendor / Party Name', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'payment_mode', 'label' => 'Payment Mode', 'type' => 'select', 'options' => ['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'cheque' => 'Cheque', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'taxable_amount', 'label' => 'Taxable Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'gst_amount', 'label' => 'GST Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'total_amount', 'label' => 'Total Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'paid_by', 'label' => 'Paid By', 'type' => 'select', 'option_model' => User::class, 'rules' => ['nullable', 'exists:users,id']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid', 'cancelled' => 'Cancelled'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'receipt_path', 'label' => 'Receipt Upload', 'type' => 'file', 'upload_dir' => 'uploads/internal-expenses', 'rules' => ['nullable', 'file', 'max:5120']], ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'attendance' => [
            'label' => 'Attendance', 'group' => 'Salesman HRMS', 'singular' => 'Attendance Record', 'model' => AttendanceLog::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'late' => 'Late', 'leave' => 'Leave'], 'can_delete' => false,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'attendance_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'check_in_at', 'label' => 'Check In', 'type' => 'datetime'], ['key' => 'check_out_at', 'label' => 'Check Out', 'type' => 'datetime'], ['key' => 'working_minutes', 'label' => 'Minutes'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'attendance_date', 'label' => 'Attendance Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'check_in_at', 'label' => 'Check In', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']], ['name' => 'check_out_at', 'label' => 'Check Out', 'type' => 'datetime-local', 'rules' => ['nullable', 'date', 'after_or_equal:check_in_at']], ['name' => 'working_minutes', 'label' => 'Working Minutes', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'late' => 'Late', 'leave' => 'Leave'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'dealer-visits' => [
            'label' => 'Dealer Visits', 'group' => 'Salesman HRMS', 'singular' => 'Dealer Visit', 'model' => DealerVisit::class, 'with' => ['salesman', 'dealer.dealerProfile'], 'sort' => ['visited_at', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'dealer.dealerProfile.firm_name', 'label' => 'Dealer'], ['key' => 'visited_at', 'label' => 'Visited At', 'type' => 'datetime'], ['key' => 'purpose', 'label' => 'Purpose'], ['key' => 'remarks', 'label' => 'Remarks']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'dealer_id', 'label' => 'Dealer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'dealer'], 'rules' => ['required', 'exists:users,id']], ['name' => 'visited_at', 'label' => 'Visited At', 'type' => 'datetime-local', 'rules' => ['required', 'date']], ['name' => 'purpose', 'label' => 'Purpose', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']]],
        ],
        'tour-plans' => [
            'label' => 'Tour Plans', 'group' => 'Salesman HRMS', 'singular' => 'Tour Plan', 'model' => TourPlan::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['planned' => 'Planned', 'approved' => 'Approved', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'plan_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'route_name', 'label' => 'Route'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'plan_date', 'label' => 'Plan Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'route_name', 'label' => 'Route Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['planned' => 'Planned', 'approved' => 'Approved', 'completed' => 'Completed', 'cancelled' => 'Cancelled'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'expenses' => [
            'label' => 'Expenses', 'group' => 'Salesman HRMS', 'singular' => 'Expense Claim', 'model' => Expense::class, 'with' => ['salesman', 'approver'], 'status_column' => 'status', 'status_options' => $approvalStatus,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'expense_type', 'label' => 'Type'], ['key' => 'expense_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'approver.name', 'label' => 'Approved By']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'expense_type', 'label' => 'Expense Type', 'type' => 'select', 'options' => ['travel' => 'Travel', 'fuel' => 'Fuel', 'food' => 'Food', 'hotel' => 'Hotel', 'mobile' => 'Mobile', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'expense_date', 'label' => 'Expense Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0.01']], ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $approvalStatus, 'rules' => ['required', 'string', 'max:40']]],
        ],
        'leaves' => [
            'label' => 'Leave', 'group' => 'Salesman HRMS', 'singular' => 'Leave Application', 'model' => LeaveApplication::class, 'with' => ['salesman', 'approver'], 'status_column' => 'status', 'status_options' => $approvalStatus,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'leave_type', 'label' => 'Leave Type'], ['key' => 'from_date', 'label' => 'From', 'type' => 'date'], ['key' => 'to_date', 'label' => 'To', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'approver.name', 'label' => 'Approved By']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'leave_type', 'label' => 'Leave Type', 'type' => 'select', 'options' => ['casual' => 'Casual', 'sick' => 'Sick', 'paid' => 'Paid', 'unpaid' => 'Unpaid', 'half_day' => 'Half Day'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'from_date', 'label' => 'From Date', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'to_date', 'label' => 'To Date', 'type' => 'date', 'rules' => ['required', 'date', 'after_or_equal:from_date']], ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $approvalStatus, 'rules' => ['required', 'string', 'max:40']]],
        ],
        'salary' => [
            'label' => 'Salary & Payroll', 'group' => 'Salesman HRMS', 'singular' => 'Salary Slip', 'model' => SalarySlip::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'salary_month', 'label' => 'Month'], ['key' => 'salary_year', 'label' => 'Year'], ['key' => 'basic_salary', 'label' => 'Basic', 'type' => 'money'], ['key' => 'incentives', 'label' => 'Incentive', 'type' => 'money'], ['key' => 'deductions', 'label' => 'Deduction', 'type' => 'money'], ['key' => 'net_salary', 'label' => 'Net Salary', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'basic_salary', 'label' => 'Basic Salary', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'allowances', 'label' => 'Allowances', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'bonus', 'label' => 'Bonus', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'incentives', 'label' => 'Incentives', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'commission', 'label' => 'Commission', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'deductions', 'label' => 'Deductions', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'net_salary', 'label' => 'Net Salary', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'salary-revisions' => [
            'label' => 'Salary Revisions', 'group' => 'Salesman HRMS', 'singular' => 'Salary Revision', 'description' => 'History of every change to a basic salary. A revision dated today or earlier is also written to the employee record; a future one is applied when it arrives.', 'model' => SalaryRevision::class, 'with' => ['salesman', 'reviser'], 'sort' => ['effective_from', 'desc'],
            'filters' => [['name' => 'reason', 'label' => 'Reason', 'column' => 'reason', 'options' => SalaryRevision::REASONS]],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'effective_from', 'label' => 'Effective From', 'type' => 'date'], ['key' => 'previous_basic', 'label' => 'Previous Basic', 'type' => 'money'], ['key' => 'new_basic', 'label' => 'New Basic', 'type' => 'money'], ['key' => 'change_amount', 'label' => 'Change', 'type' => 'money'], ['key' => 'reason_label', 'label' => 'Reason'], ['key' => 'reviser.name', 'label' => 'Revised By']],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'new_basic', 'label' => 'New Basic Salary', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0'], 'help' => 'The previous amount is read from the employee record, so it is never typed in here.'],
                ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date', 'rules' => ['required', 'date'], 'help' => 'A future date is recorded now and applied to the employee record when it arrives.'],
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'select', 'options' => SalaryRevision::REASONS, 'default' => 'increment', 'rules' => ['required', 'in:'.implode(',', array_keys(SalaryRevision::REASONS))]],
                ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:500']],
            ],
        ],
        'targets' => [
            'label' => 'Targets & Commission', 'group' => 'Salesman HRMS', 'singular' => 'Sales Target', 'model' => SalesmanTarget::class, 'with' => ['salesman'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'period_start', 'label' => 'From', 'type' => 'date'], ['key' => 'period_end', 'label' => 'To', 'type' => 'date'], ['key' => 'target_amount', 'label' => 'Target', 'type' => 'money'], ['key' => 'achieved_amount', 'label' => 'Achieved', 'type' => 'money'], ['key' => 'commission_percent', 'label' => 'Commission %']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'period_start', 'label' => 'Period Start', 'type' => 'date', 'rules' => ['required', 'date']], ['name' => 'period_end', 'label' => 'Period End', 'type' => 'date', 'rules' => ['required', 'date', 'after_or_equal:period_start']], ['name' => 'target_amount', 'label' => 'Target Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']], ['name' => 'achieved_amount', 'label' => 'Achieved Amount', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']], ['name' => 'commission_percent', 'label' => 'Commission %', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0', 'max:100']]],
        ],
        'assets' => [
            'label' => 'Salesman Assets', 'group' => 'Salesman HRMS', 'singular' => 'Asset', 'model' => SalesmanAsset::class, 'with' => ['salesman'], 'status_column' => 'status', 'status_options' => ['issued' => 'Issued', 'returned' => 'Returned', 'lost' => 'Lost', 'damaged' => 'Damaged'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'asset_type', 'label' => 'Type'], ['key' => 'asset_name', 'label' => 'Asset'], ['key' => 'serial_no', 'label' => 'Serial No.'], ['key' => 'issued_on', 'label' => 'Issued', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']], ['name' => 'asset_type', 'label' => 'Asset Type', 'type' => 'select', 'options' => ['mobile' => 'Mobile', 'laptop' => 'Laptop', 'sim' => 'SIM Card', 'vehicle' => 'Vehicle', 'other' => 'Other'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'asset_name', 'label' => 'Asset Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'serial_no', 'label' => 'Serial Number', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'issued_on', 'label' => 'Issued On', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'returned_on', 'label' => 'Returned On', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'condition', 'label' => 'Condition', 'rules' => ['nullable', 'string', 'max:255']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['issued' => 'Issued', 'returned' => 'Returned', 'lost' => 'Lost', 'damaged' => 'Damaged'], 'rules' => ['required', 'string', 'max:40']]],
        ],
        'resignations' => [
            'label' => 'Resignation & Exit', 'group' => 'Salesman HRMS', 'singular' => 'Resignation', 'description' => 'Resignation request, notice period, exit approval and full & final settlement.', 'model' => Resignation::class, 'with' => ['salesman', 'approver'], 'search' => ['reference_no'], 'status_column' => 'status', 'status_options' => $resignationStatuses, 'sort' => ['resignation_date', 'desc'],
            'columns' => [['key' => 'reference_no', 'label' => 'Reference'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'resignation_date', 'label' => 'Resigned On', 'type' => 'date'], ['key' => 'approved_last_working_date', 'label' => 'Last Working Day', 'type' => 'date'], ['key' => 'settlement_amount', 'label' => 'F&F Payable', 'type' => 'money'], ['key' => 'settlement_status', 'label' => 'Settlement'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'filters' => [['name' => 'settlement_status', 'label' => 'Settlement', 'options' => $settlementStatuses]],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'resignation_date', 'label' => 'Resignation Date', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'notice_period_days', 'label' => 'Notice Period (days)', 'type' => 'number', 'default' => 30, 'rules' => ['required', 'integer', 'min:0', 'max:365']],
                ['name' => 'notice_period_waived', 'label' => 'Notice Period Waived', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'requested_last_working_date', 'label' => 'Requested Last Working Date', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'approved_last_working_date', 'label' => 'Approved Last Working Date', 'type' => 'date', 'rules' => ['nullable', 'date'], 'help' => 'Leave blank to use the notice period.'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $resignationStatuses, 'default' => 'pending', 'rules' => ['required', 'in:'.implode(',', array_keys($resignationStatuses))]],
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
                ['name' => 'exit_interview_notes', 'label' => 'Exit Interview Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:5000']],
                ['name' => 'pending_salary', 'label' => 'Pending Salary', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'leave_encashment', 'label' => 'Leave Encashment', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'other_dues', 'label' => 'Other Dues', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'advance_recovery', 'label' => 'Advance / Loan Recovery', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'other_recovery', 'label' => 'Other Recovery', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'settlement_status', 'label' => 'Settlement Status', 'type' => 'select', 'options' => $settlementStatuses, 'default' => 'pending', 'rules' => ['required', 'in:'.implode(',', array_keys($settlementStatuses))]],
                ['name' => 'settled_on', 'label' => 'Settled On', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'assets_returned', 'label' => 'Company Assets Returned', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'documents_handed_over', 'label' => 'Exit Documents Handed Over', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'settlement_notes', 'label' => 'Settlement Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'incentive-rules' => [
            'label' => 'Incentive Rules', 'group' => 'Salesman HRMS', 'singular' => 'Incentive Rule', 'description' => 'Slabs that turn target achievement, sales or collections into an incentive.', 'model' => IncentiveRule::class, 'with' => ['department', 'designation', 'salesman'], 'search' => ['name'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Rule'], ['key' => 'basis_label', 'label' => 'Basis'], ['key' => 'slab_from', 'label' => 'From'], ['key' => 'slab_to', 'label' => 'To'], ['key' => 'reward_value', 'label' => 'Reward'], ['key' => 'applies_to', 'label' => 'Applies To'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'basis', 'label' => 'Basis', 'options' => $incentiveBases]],
            'fields' => [
                ['name' => 'name', 'label' => 'Rule Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'basis', 'label' => 'Measured On', 'type' => 'select', 'options' => $incentiveBases, 'default' => 'target_achievement', 'rules' => ['required', 'in:'.implode(',', array_keys($incentiveBases))]],
                ['name' => 'slab_from', 'label' => 'Slab From', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0'], 'help' => 'Percent for target achievement, rupees for a value basis.'],
                ['name' => 'slab_to', 'label' => 'Slab To', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0', 'gte:slab_from'], 'help' => 'Blank makes this the open-ended top slab.'],
                ['name' => 'reward_type', 'label' => 'Reward Type', 'type' => 'select', 'options' => $incentiveRewards, 'default' => 'percent', 'rules' => ['required', 'in:'.implode(',', array_keys($incentiveRewards))]],
                ['name' => 'reward_value', 'label' => 'Reward Value', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']],
                ['name' => 'max_reward', 'label' => 'Maximum Reward', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'applies_to', 'label' => 'Applies To', 'type' => 'select', 'options' => $incentiveAudience, 'default' => 'all', 'rules' => ['required', 'in:'.implode(',', array_keys($incentiveAudience))]],
                ['name' => 'department_id', 'label' => 'Department', 'type' => 'select', 'option_model' => Department::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:departments,id'], 'help' => 'Only used when Applies To is One Department.'],
                ['name' => 'designation_id', 'label' => 'Designation', 'type' => 'select', 'option_model' => Designation::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:designations,id']],
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['nullable', 'exists:users,id']],
                ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:effective_from']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'commission-rules' => [
            'label' => 'Commission Rules', 'group' => 'Salesman HRMS', 'singular' => 'Commission Rule', 'description' => 'Percentage commission on sales value, one product or one category.', 'model' => CommissionRule::class, 'with' => ['product', 'category', 'salesman'], 'search' => ['name'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Rule'], ['key' => 'basis_label', 'label' => 'Basis'], ['key' => 'commission_percent', 'label' => 'Commission %'], ['key' => 'min_sales_value', 'label' => 'Min Sales', 'type' => 'money'], ['key' => 'applies_to', 'label' => 'Applies To'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'basis', 'label' => 'Basis', 'options' => $commissionBases]],
            'fields' => [
                ['name' => 'name', 'label' => 'Rule Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'basis', 'label' => 'Applies On', 'type' => 'select', 'options' => $commissionBases, 'default' => 'sales_value', 'rules' => ['required', 'in:'.implode(',', array_keys($commissionBases))]],
                ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'option_model' => Product::class, 'rules' => ['nullable', 'exists:products,id'], 'help' => 'Only used when Applies On is One Product.'],
                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'option_model' => Category::class, 'rules' => ['nullable', 'exists:categories,id']],
                ['name' => 'commission_percent', 'label' => 'Commission %', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0', 'max:100']],
                ['name' => 'min_sales_value', 'label' => 'Minimum Sales Value', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'max_commission', 'label' => 'Maximum Commission', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'applies_to', 'label' => 'Applies To', 'type' => 'select', 'options' => $commissionAudience, 'default' => 'all', 'rules' => ['required', 'in:'.implode(',', array_keys($commissionAudience))]],
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['nullable', 'exists:users,id']],
                ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:effective_from']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'tasks' => [
            'label' => 'Tasks', 'group' => 'Salesman HRMS', 'singular' => 'Task', 'description' => 'Work assigned to a salesman, with priority, due date and completion.', 'model' => Task::class, 'with' => ['assignee', 'assigner', 'dealer'], 'search' => ['title'], 'status_column' => 'status', 'status_options' => $taskStatuses, 'sort' => ['due_date', 'asc'],
            'columns' => [['key' => 'title', 'label' => 'Task'], ['key' => 'assignee.name', 'label' => 'Assigned To'], ['key' => 'dealer.name', 'label' => 'Dealer'], ['key' => 'priority', 'label' => 'Priority'], ['key' => 'due_date', 'label' => 'Due', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'filters' => [['name' => 'priority', 'label' => 'Priority', 'options' => $taskPriorities], ['name' => 'assigned_to', 'label' => 'Salesman', 'option_model' => User::class]],
            'fields' => [
                ['name' => 'title', 'label' => 'Task Title', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'assigned_to', 'label' => 'Assign To', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'dealer_id', 'label' => 'Related Dealer', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'dealer'], 'rules' => ['nullable', 'exists:users,id']],
                ['name' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => $taskPriorities, 'default' => 'normal', 'rules' => ['required', 'in:'.implode(',', array_keys($taskPriorities))]],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $taskStatuses, 'default' => 'pending', 'rules' => ['required', 'in:'.implode(',', array_keys($taskStatuses))]],
                ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:5000']],
                ['name' => 'completion_notes', 'label' => 'Completion Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'training-programs' => [
            'label' => 'Training Programs', 'group' => 'Salesman HRMS', 'singular' => 'Training Program', 'description' => 'Scheduled training, its trainer, mode and duration.', 'model' => TrainingProgram::class, 'search' => ['title', 'trainer'], 'status_column' => 'status', 'status_options' => $trainingStatuses, 'sort' => ['starts_on', 'desc'],
            'columns' => [['key' => 'title', 'label' => 'Program'], ['key' => 'trainer', 'label' => 'Trainer'], ['key' => 'mode', 'label' => 'Mode'], ['key' => 'starts_on', 'label' => 'Starts', 'type' => 'date'], ['key' => 'ends_on', 'label' => 'Ends', 'type' => 'date'], ['key' => 'duration_hours', 'label' => 'Hours'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'filters' => [['name' => 'mode', 'label' => 'Mode', 'options' => $trainingModes]],
            'fields' => [
                ['name' => 'title', 'label' => 'Program Title', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'trainer', 'label' => 'Trainer', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => $trainingModes, 'default' => 'classroom', 'rules' => ['required', 'in:'.implode(',', array_keys($trainingModes))]],
                ['name' => 'venue', 'label' => 'Venue / Link', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'starts_on', 'label' => 'Starts On', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'ends_on', 'label' => 'Ends On', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:starts_on']],
                ['name' => 'duration_hours', 'label' => 'Duration (hours)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:1000']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $trainingStatuses, 'default' => 'planned', 'rules' => ['required', 'in:'.implode(',', array_keys($trainingStatuses))]],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:5000']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'training-attendances' => [
            'label' => 'Training Attendance', 'group' => 'Salesman HRMS', 'singular' => 'Training Attendance', 'description' => 'Who attended which training, their score and certificate.', 'model' => TrainingAttendance::class, 'with' => ['program', 'salesman'], 'status_column' => 'status', 'status_options' => $attendanceStatuses, 'sort' => ['id', 'desc'],
            'columns' => [['key' => 'program.title', 'label' => 'Program'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'score', 'label' => 'Score'], ['key' => 'certificate_issued', 'label' => 'Certificate', 'type' => 'boolean'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'filters' => [['name' => 'training_program_id', 'label' => 'Program', 'option_model' => TrainingProgram::class, 'option_label' => 'title']],
            'fields' => [
                ['name' => 'training_program_id', 'label' => 'Training Program', 'type' => 'select', 'option_model' => TrainingProgram::class, 'option_label' => 'title', 'option_where' => ['is_active' => true], 'rules' => ['required', 'exists:training_programs,id']],
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $attendanceStatuses, 'default' => 'enrolled', 'rules' => ['required', 'in:'.implode(',', array_keys($attendanceStatuses))]],
                ['name' => 'score', 'label' => 'Score', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0', 'max:100']],
                ['name' => 'certificate_issued', 'label' => 'Certificate Issued', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'certificate_path', 'label' => 'Certificate', 'type' => 'private_file', 'rules' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'], 'accept' => '.pdf,image/*'],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'employee-skills' => [
            'label' => 'Skill Records', 'group' => 'Salesman HRMS', 'singular' => 'Skill Record', 'description' => 'Skills a salesman holds and when they were certified.', 'model' => EmployeeSkill::class, 'with' => ['salesman'], 'search' => ['skill'], 'sort' => ['id', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'skill', 'label' => 'Skill'], ['key' => 'level', 'label' => 'Level'], ['key' => 'certified_on', 'label' => 'Certified On', 'type' => 'date'], ['key' => 'certified_by', 'label' => 'Certified By']],
            'filters' => [['name' => 'level', 'label' => 'Level', 'options' => $skillLevels]],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'skill', 'label' => 'Skill', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'level', 'label' => 'Level', 'type' => 'select', 'options' => $skillLevels, 'default' => 'beginner', 'rules' => ['required', 'in:'.implode(',', array_keys($skillLevels))]],
                ['name' => 'certified_on', 'label' => 'Certified On', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'certified_by', 'label' => 'Certified By', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'audit-logs' => [
            'label' => 'Audit Logs', 'group' => 'Settings', 'singular' => 'Audit Log', 'description' => 'Every create, update and delete recorded with who did it.', 'model' => AuditLog::class, 'with' => ['user'], 'search' => ['label', 'user_name', 'auditable_type'], 'sort' => ['created_at', 'desc'], 'can_create' => false, 'can_edit' => false, 'can_delete' => false,
            'columns' => [['key' => 'created_at', 'label' => 'When', 'type' => 'datetime'], ['key' => 'user_name', 'label' => 'User'], ['key' => 'event_label', 'label' => 'Action'], ['key' => 'record_type', 'label' => 'Record'], ['key' => 'auditable_id', 'label' => 'ID'], ['key' => 'label', 'label' => 'Name'], ['key' => 'change_summary', 'label' => 'Changed'], ['key' => 'ip_address', 'label' => 'IP']],
            'filters' => [['name' => 'event', 'label' => 'Action', 'options' => $auditEvents], ['name' => 'user_id', 'label' => 'User', 'option_model' => User::class]],
            'fields' => [],
        ],
        'backups' => [
            'label' => 'Backup & Restore', 'group' => 'Settings', 'singular' => 'Backup', 'description' => 'Database backups: take one, download it, restore from it.', 'model' => Backup::class, 'with' => ['creator'], 'search' => ['filename'], 'status_column' => 'status', 'status_options' => $backupStatuses, 'sort' => ['created_at', 'desc'], 'can_create' => false, 'can_edit' => false,
            'columns' => [['key' => 'created_at', 'label' => 'Taken', 'type' => 'datetime'], ['key' => 'filename', 'label' => 'File'], ['key' => 'size_label', 'label' => 'Size'], ['key' => 'table_count', 'label' => 'Tables'], ['key' => 'creator.name', 'label' => 'By'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [],
        ],
        'departments' => [
            'label' => 'Departments', 'group' => 'Salesman HRMS', 'singular' => 'Department', 'description' => 'Org chart departments used by designations and employee records.', 'model' => Department::class, 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Department'], ['key' => 'code', 'label' => 'Code'], ['key' => 'sort_order', 'label' => 'Position'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'name', 'label' => 'Department Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'code', 'label' => 'Code', 'rules' => ['required', 'string', 'max:40', 'unique:departments,code'], 'help' => 'Short unique code, e.g. SALES.'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'designations' => [
            'label' => 'Designations', 'group' => 'Salesman HRMS', 'singular' => 'Designation', 'description' => 'Job titles, optionally grouped under a department.', 'model' => Designation::class, 'with' => ['department'], 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Designation'], ['key' => 'code', 'label' => 'Code'], ['key' => 'department.name', 'label' => 'Department'], ['key' => 'level', 'label' => 'Level'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'department_id', 'label' => 'Department', 'option_model' => Department::class]],
            'fields' => [
                ['name' => 'name', 'label' => 'Designation Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'code', 'label' => 'Code', 'rules' => ['required', 'string', 'max:40', 'unique:designations,code']],
                ['name' => 'department_id', 'label' => 'Department', 'type' => 'select', 'option_model' => Department::class, 'option_where' => ['is_active' => true], 'rules' => ['nullable', 'exists:departments,id']],
                ['name' => 'level', 'label' => 'Level', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0'], 'help' => 'Higher number = more senior. Used for approval routing later.'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'leave-policies' => [
            'label' => 'Leave Policies', 'group' => 'Salesman HRMS', 'singular' => 'Leave Policy', 'description' => 'Days granted per leave type. Replaces the entitlements that used to be hardcoded.', 'model' => LeavePolicy::class, 'search' => ['leave_type', 'label'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'label', 'label' => 'Leave Type'], ['key' => 'leave_type', 'label' => 'Code'], ['key' => 'annual_days', 'label' => 'Days / Year'], ['key' => 'is_paid', 'label' => 'Paid', 'type' => 'boolean'], ['key' => 'carry_forward', 'label' => 'Carry Forward', 'type' => 'boolean'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'label', 'label' => 'Leave Type Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'leave_type', 'label' => 'Code', 'rules' => ['required', 'string', 'max:40', 'unique:leave_policies,leave_type'], 'help' => 'The code the apps send, e.g. casual, sick, earned, unpaid.'],
                ['name' => 'annual_days', 'label' => 'Days Per Year', 'type' => 'number', 'step' => '0.5', 'rules' => ['required', 'numeric', 'min:0', 'max:365']],
                ['name' => 'is_paid', 'label' => 'Paid Leave', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Unpaid types are deducted from salary when HRMS Settings says so.'],
                ['name' => 'carry_forward', 'label' => 'Allow Carry Forward', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'max_carry_forward_days', 'label' => 'Max Carry Forward Days', 'type' => 'number', 'step' => '0.5', 'rules' => ['nullable', 'numeric', 'min:0', 'max:365']],
                ['name' => 'min_notice_days', 'label' => 'Minimum Notice (days)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:365']],
                ['name' => 'max_consecutive_days', 'label' => 'Max Consecutive Days', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:365'], 'help' => '0 means no limit.'],
                ['name' => 'requires_approval', 'label' => 'Requires Approval', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'allowance-types' => [
            'label' => 'Allowance Types', 'group' => 'Salesman HRMS', 'singular' => 'Allowance Type', 'description' => 'Travel, fuel, mobile, DA and any other allowance payroll can pay.', 'model' => AllowanceType::class, 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Allowance'], ['key' => 'code', 'label' => 'Code'], ['key' => 'calculation_type', 'label' => 'Calculation'], ['key' => 'default_value', 'label' => 'Default'], ['key' => 'applies_to_all', 'label' => 'All Staff', 'type' => 'boolean'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'calculation_type', 'label' => 'Calculation', 'options' => $allowanceCalculations]],
            'fields' => [
                ['name' => 'name', 'label' => 'Allowance Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'code', 'label' => 'Code', 'rules' => ['required', 'string', 'max:40', 'unique:allowance_types,code']],
                ['name' => 'calculation_type', 'label' => 'Calculation', 'type' => 'select', 'options' => $allowanceCalculations, 'default' => 'fixed', 'rules' => ['required', 'in:'.implode(',', array_keys($allowanceCalculations))]],
                ['name' => 'default_value', 'label' => 'Default Amount / Percent', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Rupees for a fixed allowance, percent for % of basic.'],
                ['name' => 'is_taxable', 'label' => 'Taxable', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'applies_to_all', 'label' => 'Applies To Every Salesman', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'On means payroll pays it without an individual assignment.'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'deduction-types' => [
            'label' => 'Deduction Types', 'group' => 'Salesman HRMS', 'singular' => 'Deduction Type', 'description' => 'PF, ESI, Professional Tax and any other salary deduction.', 'model' => DeductionType::class, 'search' => ['name', 'code'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Deduction'], ['key' => 'code', 'label' => 'Code'], ['key' => 'calculation_type', 'label' => 'Calculation'], ['key' => 'default_value', 'label' => 'Default'], ['key' => 'statutory_kind', 'label' => 'Statutory'], ['key' => 'applies_to_all', 'label' => 'All Staff', 'type' => 'boolean'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'statutory_kind', 'label' => 'Statutory', 'options' => $statutoryKinds]],
            'fields' => [
                ['name' => 'name', 'label' => 'Deduction Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'code', 'label' => 'Code', 'rules' => ['required', 'string', 'max:40', 'unique:deduction_types,code']],
                ['name' => 'calculation_type', 'label' => 'Calculation', 'type' => 'select', 'options' => $deductionCalculations, 'default' => 'fixed', 'rules' => ['required', 'in:'.implode(',', array_keys($deductionCalculations))]],
                ['name' => 'default_value', 'label' => 'Default Amount / Percent', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'statutory_kind', 'label' => 'Statutory Type', 'type' => 'select', 'options' => $statutoryKinds, 'default' => 'none', 'rules' => ['required', 'in:'.implode(',', array_keys($statutoryKinds))], 'help' => 'PF, ESI and PT use their own legal formula and ignore the calculation above.'],
                ['name' => 'employer_share_percent', 'label' => 'Employer Share %', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0', 'max:100'], 'help' => 'Reported on the payslip, never taken from net pay.'],
                ['name' => 'wage_ceiling', 'label' => 'Wage Ceiling', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'PF caps basic at this; ESI stops applying above this gross. Blank = no ceiling.'],
                ['name' => 'applies_to_all', 'label' => 'Applies To Every Salesman', 'type' => 'checkbox', 'rules' => ['boolean']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'employee-allowances' => [
            'label' => 'Employee Allowances', 'group' => 'Salesman HRMS', 'singular' => 'Employee Allowance', 'description' => 'Which salesman gets which allowance, and from when.', 'model' => EmployeeAllowance::class, 'with' => ['salesman', 'allowanceType'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['effective_from', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'allowanceType.name', 'label' => 'Allowance'], ['key' => 'amount', 'label' => 'Amount / %'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'allowance_type_id', 'label' => 'Allowance', 'option_model' => AllowanceType::class]],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'allowance_type_id', 'label' => 'Allowance Type', 'type' => 'select', 'option_model' => AllowanceType::class, 'option_where' => ['is_active' => true], 'rules' => ['required', 'exists:allowance_types,id']],
                ['name' => 'calculation_type', 'label' => 'Calculation', 'type' => 'select', 'options' => $inheritCalculation + $allowanceCalculations, 'rules' => ['nullable', 'in:'.implode(',', array_keys($allowanceCalculations))]],
                ['name' => 'amount', 'label' => 'Amount / Percent', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to use the allowance type default.'],
                ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:effective_from'], 'help' => 'Leave blank if it continues.'],
                ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:500']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'employee-deductions' => [
            'label' => 'Employee Deductions', 'group' => 'Salesman HRMS', 'singular' => 'Employee Deduction', 'description' => 'Which salesman has which deduction, and from when.', 'model' => EmployeeDeduction::class, 'with' => ['salesman', 'deductionType'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['effective_from', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'deductionType.name', 'label' => 'Deduction'], ['key' => 'amount', 'label' => 'Amount / %'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'deduction_type_id', 'label' => 'Deduction', 'option_model' => DeductionType::class]],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'deduction_type_id', 'label' => 'Deduction Type', 'type' => 'select', 'option_model' => DeductionType::class, 'option_where' => ['is_active' => true], 'rules' => ['required', 'exists:deduction_types,id']],
                ['name' => 'calculation_type', 'label' => 'Calculation', 'type' => 'select', 'options' => $inheritCalculation + $deductionCalculations, 'rules' => ['nullable', 'in:'.implode(',', array_keys($deductionCalculations))], 'help' => 'Ignored for PF, ESI and Professional Tax.'],
                ['name' => 'amount', 'label' => 'Amount / Percent', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Leave blank to use the deduction type default.'],
                ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:effective_from'], 'help' => 'Leave blank if it continues.'],
                ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:500']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'approval-workflows' => [
            'label' => 'Approval Workflow', 'group' => 'Salesman HRMS', 'singular' => 'Approval Step', 'description' => 'Who signs off each kind of request, level by level.', 'model' => ApprovalWorkflow::class, 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['request_type', 'asc'],
            'columns' => [['key' => 'request_type_label', 'label' => 'Request'], ['key' => 'level', 'label' => 'Level'], ['key' => 'approver_role', 'label' => 'Approver'], ['key' => 'amount_from', 'label' => 'From', 'type' => 'money'], ['key' => 'amount_to', 'label' => 'To', 'type' => 'money'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'filters' => [['name' => 'request_type', 'label' => 'Request Type', 'options' => $requestTypes]],
            'fields' => [
                ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => $requestTypes, 'rules' => ['required', 'in:'.implode(',', array_keys($requestTypes))]],
                ['name' => 'level', 'label' => 'Approval Level', 'type' => 'number', 'default' => 1, 'rules' => ['required', 'integer', 'min:1', 'max:5']],
                ['name' => 'approver_role', 'label' => 'Approved By', 'type' => 'select', 'options' => $approverRoles, 'default' => 'admin', 'rules' => ['required', 'in:'.implode(',', array_keys($approverRoles))]],
                ['name' => 'amount_from', 'label' => 'Amount From', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'help' => 'Blank means the step applies to any amount.'],
                ['name' => 'amount_to', 'label' => 'Amount To', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0', 'gte:amount_from']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'holidays' => [
            'label' => 'Holidays', 'group' => 'Salesman HRMS', 'singular' => 'Holiday', 'description' => 'Holiday calendar shown in the salesman app.', 'model' => Holiday::class, 'search' => ['title'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['holiday_date', 'desc'],
            'columns' => [['key' => 'holiday_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'title', 'label' => 'Holiday'], ['key' => 'holiday_type', 'label' => 'Type'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'title', 'label' => 'Holiday Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'holiday_date', 'label' => 'Date', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'holiday_type', 'label' => 'Type', 'type' => 'select', 'options' => $holidayTypes, 'default' => 'company', 'rules' => ['required', 'in:'.implode(',', array_keys($holidayTypes))]],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'shifts' => [
            'label' => 'Shifts', 'group' => 'Salesman HRMS', 'singular' => 'Shift', 'description' => 'Working hours, grace time and weekly offs.', 'model' => Shift::class, 'with_count' => ['assignments'], 'search' => ['name'], 'status_column' => 'is_active', 'status_options' => $active,
            'columns' => [['key' => 'name', 'label' => 'Shift'], ['key' => 'starts_at', 'label' => 'Starts'], ['key' => 'ends_at', 'label' => 'Ends'], ['key' => 'grace_minutes', 'label' => 'Grace (min)'], ['key' => 'assignments_count', 'label' => 'Assigned'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'name', 'label' => 'Shift Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'starts_at', 'label' => 'Start Time', 'type' => 'time', 'rules' => ['required', 'date_format:H:i,H:i:s']],
                ['name' => 'ends_at', 'label' => 'End Time', 'type' => 'time', 'rules' => ['required', 'date_format:H:i,H:i:s']],
                ['name' => 'grace_minutes', 'label' => 'Grace Minutes', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0', 'max:600'], 'help' => 'Late marking starts after this many minutes.'],
                ['name' => 'half_day_minutes', 'label' => 'Half Day Minutes', 'type' => 'number', 'default' => 240, 'rules' => ['nullable', 'integer', 'min:0', 'max:1440'], 'help' => 'Worked less than this counts as half day.'],
                ['name' => 'weekly_offs', 'label' => 'Weekly Offs', 'type' => 'checkbox_list', 'options' => $weekDays, 'rules' => ['nullable', 'array']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],
        'shift-assignments' => [
            'label' => 'Shift Assignments', 'group' => 'Salesman HRMS', 'singular' => 'Shift Assignment', 'description' => 'Which salesman works which shift, and from when.', 'model' => ShiftAssignment::class, 'with' => ['salesman', 'shift'], 'sort' => ['effective_from', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'shift.name', 'label' => 'Shift'], ['key' => 'effective_from', 'label' => 'From', 'type' => 'date'], ['key' => 'effective_to', 'label' => 'To', 'type' => 'date']],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'shift_id', 'label' => 'Shift', 'type' => 'select', 'option_model' => Shift::class, 'option_where' => ['is_active' => true], 'rules' => ['required', 'exists:shifts,id']],
                ['name' => 'effective_from', 'label' => 'Effective From', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'effective_to', 'label' => 'Effective To', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:effective_from'], 'help' => 'Leave blank if the shift continues.'],
            ],
        ],
        'announcements' => [
            'label' => 'Announcements', 'group' => 'Salesman HRMS', 'singular' => 'Announcement', 'description' => 'Notices shown in the apps. Leave Publish At blank to keep it as a draft.', 'model' => Announcement::class, 'with' => ['author'], 'search' => ['title', 'body'], 'sort' => ['id', 'desc'], 'filters' => [['name' => 'audience', 'column' => 'audience', 'options' => $audiences]],
            'columns' => [['key' => 'title', 'label' => 'Title'], ['key' => 'audience', 'label' => 'Audience'], ['key' => 'category', 'label' => 'Category'], ['key' => 'published_at', 'label' => 'Published', 'type' => 'datetime'], ['key' => 'expires_at', 'label' => 'Expires', 'type' => 'datetime'], ['key' => 'author.name', 'label' => 'Created By']],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'audience', 'label' => 'Audience', 'type' => 'select', 'options' => $audiences, 'default' => 'salesman', 'rules' => ['required', 'in:'.implode(',', array_keys($audiences))]],
                ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => ['announcement' => 'Announcement', 'policy' => 'Policy', 'circular' => 'Circular', 'event' => 'Event'], 'default' => 'announcement', 'rules' => ['required', 'in:announcement,policy,circular,event']],
                ['name' => 'body', 'label' => 'Message', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 6, 'rules' => ['required', 'string', 'max:10000']],
                ['name' => 'published_at', 'label' => 'Publish At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
                ['name' => 'expires_at', 'label' => 'Expires At', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
                ['name' => 'attachment_path', 'label' => 'Attachment (PDF / image)', 'type' => 'file', 'accept' => '.pdf,image/*', 'upload_dir' => 'uploads/announcements', 'rules' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120']],
            ],
        ],
        'employee-documents' => [
            'label' => 'Employee Documents', 'group' => 'Salesman HRMS', 'singular' => 'Employee Document', 'description' => 'KYC and HR documents. Files are stored privately and only admins can download them.', 'model' => EmployeeDocument::class, 'with' => ['salesman'], 'search' => ['document_no'], 'status_column' => 'status', 'status_options' => $documentStatus, 'filters' => [['name' => 'document_type', 'column' => 'document_type', 'options' => $documentTypes]],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'document_type', 'label' => 'Document'], ['key' => 'document_no', 'label' => 'Number'], ['key' => 'expires_on', 'label' => 'Expires', 'type' => 'date'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'document_type', 'label' => 'Document Type', 'type' => 'select', 'options' => $documentTypes, 'rules' => ['required', 'in:'.implode(',', array_keys($documentTypes))]],
                ['name' => 'document_no', 'label' => 'Document Number', 'rules' => ['nullable', 'string', 'max:100']],
                ['name' => 'issued_on', 'label' => 'Issued On', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'expires_on', 'label' => 'Expires On', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $documentStatus, 'default' => 'pending', 'rules' => ['required', 'in:'.implode(',', array_keys($documentStatus))]],
                ['name' => 'file_path', 'label' => 'Document File (PDF / image)', 'type' => 'private_file', 'accept' => '.pdf,image/*', 'rules' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120']],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'salary-advances' => [
            'label' => 'Advances & Loans', 'group' => 'Salesman HRMS', 'singular' => 'Advance / Loan', 'description' => 'Requests raised from the salesman app. Approve, mark disbursed and record recovery here.', 'model' => SalaryAdvance::class, 'with' => ['salesman', 'approver'], 'search' => ['reference_no'], 'status_column' => 'status', 'status_options' => $advanceStatus, 'filters' => [['name' => 'advance_type', 'column' => 'advance_type', 'options' => ['advance' => 'Advance', 'loan' => 'Loan']]], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'reference_no', 'label' => 'Reference'], ['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'advance_type', 'label' => 'Type'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'installments', 'label' => 'EMIs'], ['key' => 'emi_amount', 'label' => 'EMI', 'type' => 'money'], ['key' => 'recovered_amount', 'label' => 'Recovered', 'type' => 'money'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'approver.name', 'label' => 'Approved By']],
            'fields' => [
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $advanceStatus, 'rules' => ['required', 'in:'.implode(',', array_keys($advanceStatus))]],
                ['name' => 'disbursed_on', 'label' => 'Disbursed On', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'recovered_amount', 'label' => 'Recovered Amount', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'performance-reviews' => [
            'label' => 'Performance Reviews', 'group' => 'Salesman HRMS', 'singular' => 'Performance Review', 'description' => 'Scores out of 100. Drafts stay hidden from the salesman app until published.', 'model' => PerformanceReview::class, 'with' => ['salesman', 'reviewer'], 'status_column' => 'status', 'status_options' => $reviewStatus, 'sort' => ['period_start', 'desc'],
            'columns' => [['key' => 'salesman.name', 'label' => 'Salesman'], ['key' => 'period_start', 'label' => 'From', 'type' => 'date'], ['key' => 'period_end', 'label' => 'To', 'type' => 'date'], ['key' => 'overall_rating', 'label' => 'Overall'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'reviewer.name', 'label' => 'Reviewed By']],
            'fields' => [
                ['name' => 'salesman_id', 'label' => 'Salesman', 'type' => 'select', 'option_model' => User::class, 'option_where' => ['role' => 'salesman'], 'rules' => ['required', 'exists:users,id']],
                ['name' => 'period_start', 'label' => 'Period From', 'type' => 'date', 'rules' => ['required', 'date']],
                ['name' => 'period_end', 'label' => 'Period To', 'type' => 'date', 'rules' => ['required', 'date', 'after_or_equal:period_start']],
                ['name' => 'sales_score', 'label' => 'Sales Score', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'between:0,100']],
                ['name' => 'collection_score', 'label' => 'Collection Score', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'between:0,100']],
                ['name' => 'visit_score', 'label' => 'Visit Score', 'type' => 'number', 'step' => '0.01', 'default' => 0, 'rules' => ['nullable', 'numeric', 'between:0,100']],
                ['name' => 'overall_rating', 'label' => 'Overall Rating', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'between:0,100'], 'help' => 'Leave blank to use the average of the three scores.'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $reviewStatus, 'default' => 'draft', 'rules' => ['required', 'in:'.implode(',', array_keys($reviewStatus))]],
                ['name' => 'remarks', 'label' => 'Remarks', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:5000']],
            ],
        ],
        'storefront-faqs' => [
            'label' => 'FAQs', 'group' => 'Storefront', 'singular' => 'FAQ', 'description' => 'Questions and answers shown on the website FAQ page. Visitors can search them and filter by category.', 'model' => StorefrontFaq::class, 'search' => ['question', 'answer'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'filters' => [['name' => 'category', 'label' => 'Category', 'column' => 'category', 'options' => $faqCategories]],
            'columns' => [['key' => 'question', 'label' => 'Question'], ['key' => 'category_label', 'label' => 'Category'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'question', 'label' => 'Question', 'col' => 'col-12', 'rules' => ['required', 'string', 'max:500']],
                ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 6, 'help' => 'Leave a blank line between paragraphs.', 'rules' => ['required', 'string', 'max:5000']],
                ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => $faqCategories, 'help' => 'Decides which card on the FAQ page shows this question. Leave blank to list it only under All.', 'rules' => ['nullable', 'string', 'max:60', 'in:'.implode(',', array_keys($faqCategories))]],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'contact-messages' => [
            'label' => 'Contact Messages', 'group' => 'Storefront', 'singular' => 'Contact Message', 'description' => 'Messages sent from the website Contact Us form.', 'model' => ContactMessage::class, 'with' => ['user'], 'search' => ['name', 'email', 'phone', 'subject', 'message'], 'status_column' => 'status', 'status_options' => $contactStatus, 'sort' => ['created_at', 'desc'], 'can_create' => false,
            'columns' => [['key' => 'created_at', 'label' => 'Received', 'type' => 'datetime'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email', 'type' => 'email'], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'status', 'label' => 'Status', 'type' => 'status']],
            'fields' => [
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $contactStatus, 'rules' => ['required', 'in:'.implode(',', array_keys($contactStatus))]],
            ],
        ],

        'storefront-about-items' => [
            'label' => 'About Sections', 'group' => 'Storefront', 'singular' => 'About Section Item', 'description' => 'Bullets shown next to the About Us intro text, and the figures in the "What We Do" row.', 'model' => StorefrontAboutItem::class, 'search' => ['title', 'value', 'description'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'filters' => [['name' => 'block', 'label' => 'Block', 'column' => 'block', 'options' => ['highlight' => 'Intro Bullet', 'stat' => 'What We Do Figure']]],
            'columns' => [['key' => 'icon_path', 'label' => 'Icon', 'type' => 'image'], ['key' => 'block_label', 'label' => 'Block'], ['key' => 'value', 'label' => 'Figure'], ['key' => 'title', 'label' => 'Title'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'block', 'label' => 'Block', 'type' => 'select', 'options' => ['highlight' => 'Intro Bullet', 'stat' => 'What We Do Figure'], 'help' => 'Intro bullets sit beside the About text; figures fill the "What We Do" row.', 'rules' => ['required', 'in:highlight,stat']],
                ['name' => 'title', 'label' => 'Title', 'col' => 'col-12', 'help' => 'The bullet line, or the label under the figure.', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'value', 'label' => 'Figure', 'help' => 'Only for "What We Do", e.g. 10+ or 500+.', 'rules' => ['nullable', 'string', 'max:50']],
                ['name' => 'icon_path', 'label' => 'Icon - 40 x 40 px', 'type' => 'image', 'upload_dir' => 'uploads/storefront/about', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048']],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'col' => 'col-12', 'help' => 'Only for "What We Do"; shown under the label.', 'rules' => ['nullable', 'string', 'max:1000']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'storefront-team-members' => [
            'label' => 'Team Members', 'group' => 'Storefront', 'singular' => 'Team Member', 'description' => 'People shown in the team row on the About Us page. With no active members the whole row is hidden.', 'model' => StorefrontTeamMember::class, 'search' => ['name', 'role'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'photo_path', 'label' => 'Photo', 'type' => 'image'], ['key' => 'name', 'label' => 'Name'], ['key' => 'role', 'label' => 'Role'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'role', 'label' => 'Role / Designation', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'bio', 'label' => 'Short Line', 'type' => 'textarea', 'col' => 'col-12', 'rows' => 2, 'help' => 'One short sentence shown under the role.', 'rules' => ['nullable', 'string', 'max:500']],
                ['name' => 'photo_path', 'label' => 'Photo - 350 x 350 px', 'type' => 'image', 'upload_dir' => 'uploads/storefront/team', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:2048']],
                ['name' => 'facebook_url', 'label' => 'Facebook URL', 'rules' => ['nullable', 'url', 'max:2048']],
                ['name' => 'twitter_url', 'label' => 'Twitter / X URL', 'rules' => ['nullable', 'url', 'max:2048']],
                ['name' => 'instagram_url', 'label' => 'Instagram URL', 'rules' => ['nullable', 'url', 'max:2048']],
                ['name' => 'linkedin_url', 'label' => 'LinkedIn URL', 'rules' => ['nullable', 'url', 'max:2048']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'storefront-footer-links' => [
            'label' => 'Footer Links', 'group' => 'Storefront', 'singular' => 'Footer Link', 'description' => 'Links shown in the website footer columns.', 'model' => StorefrontFooterLink::class, 'search' => ['title', 'url', 'link_group'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'link_group', 'label' => 'Footer Column'], ['key' => 'title', 'label' => 'Link Title'], ['key' => 'url', 'label' => 'URL'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'link_group', 'label' => 'Footer Column', 'type' => 'select', 'options' => ['about' => 'About Store', 'useful' => 'Useful Links', 'help' => 'Help Center', 'categories' => 'Categories'], 'rules' => ['required', 'string', 'max:80']],
                ['name' => 'title', 'label' => 'Link Title', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'url', 'label' => 'URL', 'help' => 'Full URL, or a path such as /about-us', 'rules' => ['required', 'string', 'max:2048']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'storefront-topbar-messages' => [
            'label' => 'Top Bar Messages', 'group' => 'Settings', 'singular' => 'Top Bar Message', 'description' => 'Messages that slide in the dark strip at the top of the website header. Turn all of them off to hide the strip.', 'model' => StorefrontTopbarMessage::class, 'search' => ['heading', 'message', 'link_label'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'heading', 'label' => 'Bold Heading'], ['key' => 'message', 'label' => 'Message'], ['key' => 'link_label', 'label' => 'Link Text'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'heading', 'label' => 'Bold Heading', 'help' => 'Optional. Shown in bold before the message.', 'rules' => ['nullable', 'string', 'max:255', 'required_without:message']],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['nullable', 'string', 'max:500', 'required_without:heading']],
                ['name' => 'link_label', 'label' => 'Link Text', 'help' => 'Optional, e.g. Buy Now', 'rules' => ['nullable', 'string', 'max:100', 'required_with:link_url']],
                ['name' => 'link_url', 'label' => 'Link URL', 'help' => 'Full URL, or a path such as /shop-left-sidebar', 'rules' => ['nullable', 'string', 'max:2048', 'required_with:link_label']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'delivery-areas' => [
            'label' => 'Delivery Areas', 'group' => 'Storefront', 'singular' => 'Delivery Area', 'description' => 'Districts listed in the website "Your Location" box. Until you add one, every Maharashtra district is listed.', 'model' => DeliveryArea::class, 'search' => ['district_name', 'state_name'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'filters' => [['name' => 'state_code', 'label' => 'State', 'column' => 'state_code', 'option_model' => LgdState::class, 'option_value' => 'state_code', 'option_label' => 'name']],
            'columns' => [['key' => 'district_name', 'label' => 'District'], ['key' => 'state_name', 'label' => 'State'], ['key' => 'min_order_amount', 'label' => 'Minimum Order', 'type' => 'money'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['type' => 'district_picker', 'name' => 'district', 'label' => 'District'],
                ['name' => 'min_order_amount', 'label' => 'Minimum Order (₹)', 'type' => 'number', 'step' => '0.01', 'help' => 'Leave blank to show no minimum.', 'rules' => ['nullable', 'numeric', 'min:0']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'storefront-service-blocks' => [
            'label' => 'Service Blocks', 'group' => 'Storefront', 'singular' => 'Service Block', 'description' => 'The promise strip shown above the website footer.', 'model' => StorefrontServiceBlock::class, 'search' => ['title', 'subtitle'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['sort_order', 'asc'],
            'columns' => [['key' => 'icon_path', 'label' => 'Icon', 'type' => 'image'], ['key' => 'title', 'label' => 'Title'], ['key' => 'subtitle', 'label' => 'Subtitle'], ['key' => 'sort_order', 'label' => 'Sort Order'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'subtitle', 'label' => 'Subtitle', 'rules' => ['nullable', 'string', 'max:255']],
                ['name' => 'icon_path', 'label' => 'Icon - 40 x 40 px', 'type' => 'image', 'upload_dir' => 'uploads/storefront/service-blocks', 'rules' => ['nullable', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0, 'rules' => ['nullable', 'integer', 'min:0']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'web-translations' => [
            'label' => 'Website Translations', 'group' => 'Translation', 'singular' => 'Website Translation', 'description' => 'Storefront and mobile-app text. Rows are created automatically the first time a string is shown in a language; edit any wrong wording here.', 'model' => WebTranslation::class, 'search' => ['translation_key', 'english_text', 'value', 'locale'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['id', 'desc'],
            'columns' => [['key' => 'locale', 'label' => 'Language'], ['key' => 'group', 'label' => 'Group'], ['key' => 'translation_key', 'label' => 'Key'], ['key' => 'english_text', 'label' => 'English'], ['key' => 'value', 'label' => 'Translation'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [
                ['name' => 'locale', 'label' => 'Language', 'type' => 'select', 'option_model' => Language::class, 'option_where' => ['is_active' => 1], 'option_value' => 'code', 'option_label' => 'name', 'rules' => ['required', 'string', 'max:10']],
                ['name' => 'group', 'label' => 'Group', 'help' => 'The part before the first dot in the key, e.g. nav, footer, cart.', 'rules' => ['nullable', 'string', 'max:80']],
                ['name' => 'translation_key', 'label' => 'Translation Key', 'rules' => ['required', 'string', 'max:255']],
                ['name' => 'english_text', 'label' => 'English Text', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                ['name' => 'value', 'label' => 'Translated Text', 'type' => 'textarea', 'rules' => ['required', 'string']],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']],
            ],
        ],

        'admin-users' => [
            'label' => 'Admin Users', 'group' => 'Settings', 'singular' => 'Admin User', 'description' => 'Staff who can sign in to this admin panel. Each user sees only the sections their role allows.', 'model' => User::class, 'where' => ['role' => User::ROLE_ADMIN], 'with' => ['adminRole'], 'search' => ['name', 'email', 'mobile'], 'status_column' => 'status', 'status_options' => ['active' => 'Active', 'inactive' => 'Inactive'],
            'filters' => [['name' => 'admin_role_id', 'label' => 'Role', 'column' => 'admin_role_id', 'option_model' => AdminRole::class]],
            'columns' => [['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'mobile', 'label' => 'Mobile'], ['key' => 'adminRole.name', 'label' => 'Role'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'last_login_at', 'label' => 'Last Login', 'type' => 'datetime']],
            'fields' => [['name' => 'name', 'label' => 'Full Name', 'rules' => ['required', 'string', 'max:255']], ['name' => 'email', 'label' => 'Email (login)', 'type' => 'email', 'rules' => ['required', 'email', 'max:255', 'unique:users,email,{id}']], ['name' => 'mobile', 'label' => 'Mobile', 'rules' => ['nullable', 'string', 'max:20', 'unique:users,mobile,{id}']], ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => ['required', 'string', 'min:8', 'confirmed']], ['name' => 'admin_role_id', 'label' => 'Role', 'type' => 'select', 'option_model' => AdminRole::class, 'rules' => ['required', 'exists:admin_roles,id']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'default' => 'active', 'rules' => ['required', 'in:active,inactive']]],
        ],
        'admin-roles' => [
            'label' => 'Roles & Permissions', 'group' => 'Settings', 'singular' => 'Role', 'description' => 'A role decides which sections its users can view, add, edit and delete.', 'model' => AdminRole::class, 'with_count' => ['users', 'permissions'], 'search' => ['name', 'description'], 'sort' => ['id', 'asc'],
            'columns' => [['key' => 'name', 'label' => 'Role'], ['key' => 'description', 'label' => 'Description'], ['key' => 'users_count', 'label' => 'Users'], ['key' => 'permissions_count', 'label' => 'Sections'], ['key' => 'updated_at', 'label' => 'Updated', 'type' => 'datetime']],
            'fields' => [['name' => 'name', 'label' => 'Role Name', 'rules' => ['required', 'string', 'max:100', 'unique:admin_roles,name,{id}']], ['name' => 'description', 'label' => 'Description', 'rules' => ['nullable', 'string', 'max:255']]],
        ],
        'notifications' => [
            'label' => 'Notifications', 'group' => 'Settings', 'singular' => 'Notification', 'model' => Notification::class, 'with' => ['user'], 'search' => ['title', 'message'], 'can_delete' => true,
            'columns' => [['key' => 'user.name', 'label' => 'Recipient'], ['key' => 'channel', 'label' => 'Channel'], ['key' => 'title', 'label' => 'Title'], ['key' => 'message', 'label' => 'Message'], ['key' => 'read_at', 'label' => 'Read At', 'type' => 'datetime'], ['key' => 'created_at', 'label' => 'Sent', 'type' => 'datetime']],
            'fields' => [['name' => 'audience', 'label' => 'Send To', 'type' => 'select', 'options' => ['user' => 'One user', 'customer' => 'All customers', 'dealer' => 'All dealers', 'salesman' => 'All salesmen', 'all' => 'Everyone'], 'default' => 'user', 'rules' => ['nullable', 'in:user,customer,dealer,salesman,all'], 'help' => 'Only used when adding. A group message goes to every active account in that group.'], ['name' => 'user_id', 'label' => 'Recipient (for One user)', 'type' => 'select', 'option_model' => User::class, 'rules' => ['nullable', 'exists:users,id']], ['name' => 'channel', 'label' => 'Channel', 'type' => 'select', 'options' => ['push' => 'Push', 'email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'in_app' => 'In App'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'title', 'label' => 'Title', 'rules' => ['required', 'string', 'max:255']], ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['required', 'string']]],
        ],
        'languages' => [
            'label' => 'Languages', 'group' => 'Translation', 'singular' => 'Language', 'model' => Language::class, 'search' => ['code', 'name', 'native_name'], 'status_column' => 'is_active', 'status_options' => $active, 'can_delete' => false,
            'columns' => [['key' => 'code', 'label' => 'Code'], ['key' => 'name', 'label' => 'English Name'], ['key' => 'native_name', 'label' => 'Native Name'], ['key' => 'is_default', 'label' => 'Default', 'type' => 'boolean'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean'], ['key' => 'sort_order', 'label' => 'Sort']],
            'fields' => [['name' => 'code', 'label' => 'Language Code', 'rules' => ['required', 'string', 'max:10', 'unique:languages,code,{id}'], 'help' => 'Use stable locale keys like en, hi, mr, gu, kn, te. English remains the app fallback/default.'], ['name' => 'name', 'label' => 'English Name', 'rules' => ['required', 'string', 'max:80']], ['name' => 'native_name', 'label' => 'Native Name', 'rules' => ['nullable', 'string', 'max:120']], ['name' => 'is_default', 'label' => 'Default Language', 'type' => 'checkbox', 'rules' => ['boolean'], 'help' => 'Keep English as default. Only one default should be active.'], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']], ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']]],
        ],        'translations' => [
            'label' => 'App Translations', 'group' => 'Translation', 'singular' => 'Translation', 'description' => 'Customer, dealer and salesman app text. The apps register their English text automatically; press Translate to fill every language, then correct any wrong wording here.', 'model' => AppTranslation::class, 'search' => ['translation_key', 'english_text', 'value', 'locale'], 'status_column' => 'is_active', 'status_options' => $active, 'sort' => ['id', 'desc'],
            'filters' => [
                ['name' => 'app', 'label' => 'App', 'column' => 'app', 'options' => ['customer' => 'Customer App', 'dealer' => 'Dealer App', 'salesman' => 'Salesman App']],
                ['name' => 'locale', 'label' => 'Language', 'column' => 'locale', 'option_model' => Language::class, 'option_value' => 'code', 'option_label' => 'name'],
                ['name' => 'source', 'label' => 'Source', 'column' => 'source', 'options' => ['website' => 'Website', 'app' => 'Another App', 'google' => 'Google', 'manual' => 'Manual']],
            ],
            'columns' => [['key' => 'app', 'label' => 'App'], ['key' => 'locale', 'label' => 'Language'], ['key' => 'translation_key', 'label' => 'Key'], ['key' => 'english_text', 'label' => 'English'], ['key' => 'value', 'label' => 'Translation'], ['key' => 'source', 'label' => 'Source'], ['key' => 'is_active', 'label' => 'Status', 'type' => 'boolean']],
            'fields' => [['name' => 'app', 'label' => 'App', 'type' => 'select', 'options' => ['customer' => 'Customer App', 'dealer' => 'Dealer App', 'salesman' => 'Salesman App'], 'rules' => ['required', 'in:customer,dealer,salesman']], ['name' => 'locale', 'label' => 'Language', 'type' => 'select', 'option_model' => Language::class, 'option_where' => ['is_active' => 1], 'option_value' => 'code', 'option_label' => 'name', 'rules' => ['required', 'string', 'max:10']], ['name' => 'group', 'label' => 'Group', 'help' => 'The part before the first dot in the key, e.g. cart, orders.', 'rules' => ['required', 'string', 'max:80']], ['name' => 'translation_key', 'label' => 'Translation Key', 'rules' => ['required', 'string', 'max:190']], ['name' => 'english_text', 'label' => 'English Text', 'type' => 'textarea', 'rules' => ['nullable', 'string']], ['name' => 'value', 'label' => 'Translated Text', 'type' => 'textarea', 'rules' => ['nullable', 'string']], ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'rules' => ['boolean']]],
        ],
        'support' => [
            'label' => 'Support', 'group' => 'Settings', 'singular' => 'Support Ticket', 'model' => SupportTicket::class, 'with' => ['user'], 'search' => ['ticket_no', 'subject', 'message'], 'status_column' => 'status', 'status_options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'], 'can_create' => false, 'can_delete' => false,
            'columns' => [['key' => 'ticket_no', 'label' => 'Ticket'], ['key' => 'user.name', 'label' => 'User'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'status', 'label' => 'Status', 'type' => 'status'], ['key' => 'created_at', 'label' => 'Created', 'type' => 'datetime']],
            'fields' => [['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'], 'rules' => ['required', 'string', 'max:40']], ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'col' => 'col-12', 'rules' => ['required', 'string']]],
        ],
    ],
];
