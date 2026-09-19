<?php

namespace App\Contracts\Hr;

use App\Models\Hr\Resignation;

interface EmployeeExitContract
{
    /**
     * Suggested full & final figures for a resignation: unpaid approved
     * payslips, leave encashment on the unused paid balance, and whatever is
     * still outstanding on the employee's advances and loans.
     *
     * @return array{pending_salary: float, leave_encashment: float, advance_recovery: float}
     */
    public function settlementSuggestion(Resignation $resignation): array;

    /**
     * Keep the employee record in step with the resignation: notice period
     * while it is pending or approved, exited once it is completed.
     */
    public function syncEmploymentStatus(Resignation $resignation): void;
}
