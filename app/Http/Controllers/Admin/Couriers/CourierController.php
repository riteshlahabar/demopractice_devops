<?php

namespace App\Http\Controllers\Admin\Couriers;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Http\Request;

class CourierController extends AdminModuleController
{
    protected string $moduleKey = 'couriers';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        if (empty($data['courier_code'])) {
            $data['courier_code'] = 'CR'.now()->format('ymdHis').random_int(100, 999);
        }

        return $data;
    }
}
