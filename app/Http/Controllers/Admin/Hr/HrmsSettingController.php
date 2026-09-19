<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Contracts\Hr\HrmsSettingsContract;
use App\Http\Controllers\Controller;
use App\Models\Hr\HrmsSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Attendance and salary rules — the two parts of HRMS Settings that are a
 * single row rather than a list. The list-shaped parts (departments,
 * designations, leave policies, allowance and deduction types, approval
 * workflows) are ordinary config-driven modules.
 */
class HrmsSettingController extends Controller
{
    public const WEEKDAYS = [
        'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
        'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
    ];

    public function __construct(private readonly HrmsSettingsContract $settings) {}

    public function edit(): View
    {
        return view('admin.hr.settings', [
            'setting' => HrmsSetting::query()->orderBy('id')->first() ?: new HrmsSetting,
            'weekdays' => self::WEEKDAYS,
            'workingDaysBasis' => HrmsSetting::WORKING_DAYS_BASIS,
            'roundingModes' => HrmsSetting::ROUNDING_MODES,
            'pageTitle' => 'HRMS Settings',
            'breadcrumbs' => ['Admin', 'HRMS', 'Settings'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'half_day_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'full_day_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'late_marks_per_absent' => ['required', 'integer', 'min:0', 'max:31'],
            'auto_mark_absent' => ['boolean'],
            'default_weekly_offs' => ['nullable', 'array'],
            'default_weekly_offs.*' => ['string', 'in:'.implode(',', array_keys(self::WEEKDAYS))],
            'working_days_basis' => ['required', 'in:'.implode(',', array_keys(HrmsSetting::WORKING_DAYS_BASIS))],
            'fixed_working_days' => ['required', 'integer', 'min:1', 'max:31'],
            'payroll_cycle_day' => ['required', 'integer', 'min:1', 'max:28'],
            'deduct_unpaid_leave' => ['boolean'],
            'deduct_absent_days' => ['boolean'],
            'rounding_mode' => ['required', 'in:'.implode(',', array_keys(HrmsSetting::ROUNDING_MODES))],
        ]);

        $validated['half_day_minutes'] = min($validated['half_day_minutes'], $validated['full_day_minutes']);
        foreach (['auto_mark_absent', 'deduct_unpaid_leave', 'deduct_absent_days'] as $flag) {
            $validated[$flag] = $request->boolean($flag);
        }
        $validated['default_weekly_offs'] = array_values($validated['default_weekly_offs'] ?? []);

        $setting = HrmsSetting::query()->orderBy('id')->first() ?: new HrmsSetting;
        $setting->fill($validated)->save();
        $this->settings->forget();

        return back()->with('success', 'HRMS settings updated.');
    }
}
