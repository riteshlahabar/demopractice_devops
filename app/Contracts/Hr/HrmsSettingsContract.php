<?php

namespace App\Contracts\Hr;

use App\Models\Hr\HrmsSetting;

interface HrmsSettingsContract
{
    /**
     * The single HRMS settings row. Never null — an unsaved model carrying the
     * schema defaults is returned when the row is missing or the DB is down,
     * so payroll and attendance never break on a settings lookup.
     */
    public function current(): HrmsSetting;

    public function forget(): void;
}
