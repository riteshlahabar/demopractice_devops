<?php

namespace App\Http\Controllers\Admin\Salesmen;

use App\Http\Controllers\Admin\Concerns\PeopleModuleController;
use App\Models\SalesmanProfile;
use App\Models\User;
use Illuminate\Http\Request;

class SalesmanController extends PeopleModuleController
{
    protected string $moduleKey = 'salesmen';

    protected string $role = User::ROLE_SALESMAN;

    protected string $profileRelation = 'salesmanProfile';

    protected string $profileModel = SalesmanProfile::class;

    protected array $profileFields = [
        'employee_code', 'department_id', 'designation_id', 'reporting_to',
        'joining_date', 'confirmation_date', 'employment_status', 'exit_date',
        'basic_salary', 'target_amount', 'territory',
    ];

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        $data['basic_salary'] = blank($data['basic_salary'] ?? null) ? 0 : $data['basic_salary'];
        $data['target_amount'] = blank($data['target_amount'] ?? null) ? 0 : $data['target_amount'];

        return $data;
    }
}
