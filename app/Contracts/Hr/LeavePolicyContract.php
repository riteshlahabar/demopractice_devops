<?php

namespace App\Contracts\Hr;

interface LeavePolicyContract
{
    /**
     * Leave type => days granted per calendar year, taken from the Leave
     * Policies master and falling back to config/hrms.php when it is empty.
     *
     * @return array<string, float>
     */
    public function entitlements(): array;

    /**
     * Leave types that are paid, so payroll knows which absences to deduct.
     *
     * @return array<int, string>
     */
    public function paidTypes(): array;

    public function forget(): void;
}
