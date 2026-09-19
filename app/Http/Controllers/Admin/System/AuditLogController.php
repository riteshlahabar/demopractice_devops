<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;

/**
 * Read-only by design — the module config switches off create, edit and
 * delete, so the trail cannot be rewritten from the panel that it audits.
 */
class AuditLogController extends AdminModuleController
{
    protected string $moduleKey = 'audit-logs';
}
