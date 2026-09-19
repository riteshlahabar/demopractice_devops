<?php

namespace App\Http\Controllers\Admin\InternalExpenses;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;

class InternalExpenseController extends AdminModuleController
{
    protected string $moduleKey = 'internal-expenses';
}
