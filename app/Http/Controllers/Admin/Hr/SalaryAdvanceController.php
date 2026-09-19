<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Model;

class SalaryAdvanceController extends AdminModuleController
{
    protected string $moduleKey = 'salary-advances';

    private const APPROVED_STATUSES = ['approved', 'disbursed', 'closed'];

    /**
     * Amount and EMI schedule come from the salesman's request and are not
     * editable here; recovery can never exceed the sanctioned amount, and the
     * first move into an approved state records who approved it.
     */
    protected function persist(array $data, ?Model $record): Model
    {
        if ($record) {
            $data['recovered_amount'] = min((float) ($data['recovered_amount'] ?? 0), (float) $record->amount);

            if (in_array($data['status'] ?? '', self::APPROVED_STATUSES, true) && ! $record->approved_at) {
                $data['approved_by'] = auth()->id();
                $data['approved_at'] = now();
            }
        }

        return parent::persist($data, $record);
    }
}
