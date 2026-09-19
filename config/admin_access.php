<?php

/*
 * Admin panel permissions.
 *
 * Every sidebar item (config admin.groups, plus each report) is a section a
 * role can be given View / Add / Edit / Delete on. This file maps every admin
 * route onto one of those sections and actions. A route that cannot be
 * mapped is refused for everyone except Super Admin, so a new page is locked
 * until it is added here or follows the `admin.{module}.{action}` naming.
 */
return [
    'action_labels' => ['view' => 'View', 'create' => 'Add', 'edit' => 'Edit', 'delete' => 'Delete'],

    /*
     * Sections that do not offer all four actions. Keys ending in * match a
     * prefix. Module pages also drop the actions their module switches off
     * (can_create / can_edit / can_delete = false in config/admin.php).
     */
    'section_actions' => [
        'dashboard-erp' => ['view'],
        'dashboard-hrms' => ['view'],
        'reports-overview' => ['view'],
        'report-*' => ['view'],
        'email-templates' => ['view'],
        'company-settings' => ['view', 'edit'],
        'storefront-about' => ['view', 'edit'],
        'hrms-settings' => ['view', 'edit'],
        // The audit trail is read-only on purpose; backups are taken and
        // restored, never typed in, so they offer no Add or Edit.
        'audit-logs' => ['view'],
        'backups' => ['view', 'create', 'delete'],
        'app-languages' => ['view', 'edit'],
        'bulk-attendance' => ['view', 'create'],
    ],

    // Last part of a route name => action.
    'route_actions' => [
        'index' => 'view', 'show' => 'view', 'export' => 'view', 'sample' => 'view', 'download' => 'view', 'print' => 'view', 'pdf' => 'view',
        'create' => 'create', 'store' => 'create',
        'edit' => 'edit', 'update' => 'edit', 'generate' => 'edit', 'status' => 'edit', 'decision' => 'edit', 'approve' => 'edit', 'cancel' => 'edit',
        'convert-to-proforma' => 'edit', 'convert-to-invoice' => 'edit', 'translate' => 'edit', 'translate-batch' => 'edit',
        'suggest-settlement' => 'edit',
        // Taking a backup creates one; restoring overwrites the database, so
        // it is the most destructive action the panel offers and is mapped to
        // delete rather than edit.
        'run' => 'create', 'restore' => 'delete',
        'destroy' => 'delete', 'bulk-destroy' => 'delete',
    ],

    // Routes whose name does not follow admin.{module}.{action}. null = open to any admin.
    'routes' => [
        'admin.logout' => null,
        'admin.dashboard' => ['dashboard-erp', 'view'],
        'admin.dashboard.hrms' => ['dashboard-hrms', 'view'],
        'admin.attendance.bulk' => ['bulk-attendance', 'view'],
        'admin.attendance.bulk.store' => ['bulk-attendance', 'create'],
        'admin.reports.index' => ['reports-overview', 'view'],
        'admin.report.show' => ['report-{report}', 'view'],
        'admin.report.export' => ['report-{report}', 'view'],
        'admin.company-settings.edit' => ['company-settings', 'view'],
        'admin.company-settings.update' => ['company-settings', 'edit'],
        'admin.storefront-about.edit' => ['storefront-about', 'view'],
        'admin.storefront-about.update' => ['storefront-about', 'edit'],
        'admin.app-languages.edit' => ['app-languages', 'view'],
        'admin.app-languages.update' => ['app-languages', 'edit'],
        'admin.products.images.destroy' => ['products', 'edit'],
        'admin.products.field-image.destroy' => ['products', 'edit'],
        'admin.common-import.sample' => ['{module}', 'view'],
        'admin.common-import.store' => ['{module}', 'create'],
    ],

    // Route modules that are managed from another section's page.
    'module_sections' => [
        'homepage-setting-items' => 'homepage-settings',
        'pricing' => 'products',
    ],

    /*
     * Sales modules have separate Customer and Dealer sections
     * (customer-orders / dealer-orders ...). The channel comes from the
     * record's order, never only from the ?type= link.
     */
    'channels' => ['customer', 'dealer'],
    'channel_modules' => ['orders', 'proforma-invoices', 'invoices', 'dispatches', 'returns'],
    'sales_documents' => ['order' => 'orders', 'proforma' => 'proforma-invoices', 'invoice' => 'invoices'],
];
