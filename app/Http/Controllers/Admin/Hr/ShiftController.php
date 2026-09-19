<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Http\Request;

class ShiftController extends AdminModuleController
{
    protected string $moduleKey = 'shifts';

    /**
     * Weekly offs arrive as ticked day numbers; unticking every box sends
     * nothing, so the list is always rebuilt from the request.
     */
    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $days = array_map('intval', (array) $request->input('weekly_offs', []));
        $validated['weekly_offs'] = array_values(array_unique(array_filter($days, fn (int $day): bool => $day >= 1 && $day <= 7)));
        sort($validated['weekly_offs']);

        return parent::prepareData($validated, $request, $module);
    }
}
