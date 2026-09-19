<?php

use App\Providers\AdminAccessServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuditServiceProvider;
use App\Providers\CatalogServiceProvider;
use App\Providers\NotificationServiceProvider;
use App\Providers\ReportServiceProvider;
use App\Providers\StorefrontServiceProvider;

return [
    AppServiceProvider::class,
    AdminAccessServiceProvider::class,
    AuditServiceProvider::class,
    CatalogServiceProvider::class,
    NotificationServiceProvider::class,
    ReportServiceProvider::class,
    StorefrontServiceProvider::class,
];
