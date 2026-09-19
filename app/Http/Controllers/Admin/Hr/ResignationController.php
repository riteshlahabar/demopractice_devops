<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Contracts\Hr\EmployeeExitContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Hr\Resignation;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResignationController extends AdminModuleController
{
    protected string $moduleKey = 'resignations';

    public function __construct(
        AdminModuleServices $modules,
        private readonly EmployeeExitContract $exits,
    ) {
        parent::__construct($modules);
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        $data['reference_no'] = $data['reference_no'] ?? 'RES-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));

        foreach (['pending_salary', 'leave_encashment', 'other_dues', 'advance_recovery', 'other_recovery'] as $money) {
            $data[$money] = blank($data[$money] ?? null) ? 0 : $data[$money];
        }

        return $data;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        // The last working date follows the notice period unless the admin
        // overrode it, and the settlement is always dues minus recoveries so
        // the two can never drift apart.
        $resignation = $record instanceof Resignation ? $record : new Resignation;
        $draft = (clone $resignation)->fill($data);

        if (blank($data['approved_last_working_date'] ?? null)) {
            $data['approved_last_working_date'] = $draft->noticePeriodEndsOn();
        }

        $data['settlement_amount'] = $draft->computedSettlement();

        if (($data['status'] ?? null) === 'approved' && $resignation->approved_at === null) {
            $data['approved_by'] = $data['approved_by'] ?? auth()->id();
            $data['approved_at'] = now();
        }

        $saved = parent::persist($data, $record);

        if ($saved instanceof Resignation) {
            $this->exits->syncEmploymentStatus($saved);
        }

        return $saved;
    }

    /**
     * Fill the full & final boxes from what the system already knows: unpaid
     * approved payslips, unused paid leave and outstanding advances. The admin
     * can still change every figure before saving.
     */
    public function suggestSettlement(int|string $id): RedirectResponse
    {
        $resignation = Resignation::query()->findOrFail($id);
        $suggestion = $this->exits->settlementSuggestion($resignation);

        $resignation->fill($suggestion);
        $resignation->settlement_amount = $resignation->computedSettlement();
        $resignation->save();

        return back()->with('success', 'Full & final figures filled from payroll, leave and advance records. Review them before settling.');
    }
}
