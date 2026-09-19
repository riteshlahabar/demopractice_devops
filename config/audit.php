<?php

use App\Models\Catalog\ProductTranslation;
use App\Models\Communication\AppTranslation;
use App\Models\Communication\DeviceToken;
use App\Models\Communication\Notification;
use App\Models\Communication\WebTranslation;
use App\Models\Location\LgdDistrict;
use App\Models\Location\LgdLocalBody;
use App\Models\Location\LgdState;
use App\Models\System\AuditLog;
use App\Models\System\Backup;

return [
    /*
    |--------------------------------------------------------------------------
    | Audit logging
    |--------------------------------------------------------------------------
    |
    | Recording is on by default and can be switched off with AUDIT_ENABLED=false
    | if a bulk import ever needs to run without filling the trail.
    |
    */
    'enabled' => (bool) env('AUDIT_ENABLED', true),

    /*
    | Models that are never audited. The audit and backup tables themselves are
    | excluded so recording cannot recurse; the rest are high-volume or
    | machine-written rows whose history has no investigative value and would
    | bury the real changes.
    */
    'ignore' => [
        AuditLog::class,
        Backup::class,
        AppTranslation::class,
        WebTranslation::class,
        DeviceToken::class,
        Notification::class,
        ProductTranslation::class,
        LgdState::class,
        LgdDistrict::class,
        LgdLocalBody::class,
    ],

    /*
    | Columns whose value is never written to the trail. The fact that the
    | column changed is still recorded; the value is replaced with a marker.
    */
    'redact' => [
        'password', 'remember_token', 'token', 'api_token', 'access_token',
        'refresh_token', 'secret', 'encryption_key', 'private_key',
    ],

    /*
    | Columns whose changes are not worth a row on their own. A save that only
    | touches these is not recorded at all.
    */
    'ignore_columns' => ['updated_at', 'created_at', 'remember_token', 'last_login_at'],

    /*
    | First of these attributes that exists becomes the row's readable label.
    */
    'label_attributes' => ['name', 'title', 'label', 'order_no', 'invoice_no', 'reference_no', 'code', 'email'],

    /*
    | Trail rows older than this are deleted by `audit:prune`. Null keeps them
    | forever.
    */
    'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),
];
