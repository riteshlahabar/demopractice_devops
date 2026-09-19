<?php

namespace App\Http\Controllers\Admin\Collections;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;

/**
 * Only payments a salesman collected; the scope lives in the module config
 * (`where_not_null`) so the listing, edit and export all apply it.
 */
class CollectionController extends AdminModuleController
{
    protected string $moduleKey = 'collections';
}
