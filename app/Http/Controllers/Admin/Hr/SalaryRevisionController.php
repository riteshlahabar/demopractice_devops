<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Contracts\Hr\SalaryRevisionContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Hr\SalaryRevision;
use App\Models\SalesmanProfile;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SalaryRevisionController extends AdminModuleController
{
    protected string $moduleKey = 'salary-revisions';

    public function __construct(
        AdminModuleServices $modules,
        private readonly SalaryRevisionContract $revisions,
    ) {
        parent::__construct($modules);
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        $data['revised_by'] = auth()->id();
        $data['source'] = 'manual';

        return $data;
    }

    /**
     * On a new revision the previous salary is never typed in — it is whatever
     * the employee record holds now, so the two can never disagree. Saving also
     * moves the employee record on, unless the revision is dated in the future;
     * recording is suppressed there, so no second history row is written.
     */
    protected function persist(array $data, ?Model $record): Model
    {
        if ($record === null && ($data['salesman_id'] ?? null)) {
            $data['previous_basic'] = SalesmanProfile::query()
                ->where('user_id', $data['salesman_id'])
                ->value('basic_salary');
        }

        $revision = parent::persist($data, $record);

        if ($revision instanceof SalaryRevision) {
            $this->revisions->apply($revision);
        }

        return $revision;
    }
}
