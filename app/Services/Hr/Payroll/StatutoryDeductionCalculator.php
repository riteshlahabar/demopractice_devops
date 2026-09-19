<?php

namespace App\Services\Hr\Payroll;

use App\Models\Hr\DeductionType;

/**
 * PF, ESI and Professional Tax. These do not follow the plain
 * fixed/percentage formula: PF is charged on basic capped at the wage ceiling,
 * ESI on gross and only while gross stays within its ceiling, and PT is a flat
 * slab amount. Each also carries an employer share that is reported but never
 * taken out of the employee's net pay.
 */
class StatutoryDeductionCalculator
{
    /**
     * Employee share of one statutory deduction.
     */
    public function employeeShare(DeductionType $type, float $basic, float $gross): float
    {
        return match ($type->statutory_kind) {
            'pf' => $this->percentOf($this->capped($basic, $type), (float) $type->default_value),
            'esi' => $this->withinCeiling($gross, $type)
                ? $this->percentOf($gross, (float) $type->default_value)
                : 0.0,
            'professional_tax' => $gross > 0 ? round((float) $type->default_value, 2) : 0.0,
            default => 0.0,
        };
    }

    /**
     * Employer share, reported on the payslip but not deducted from net pay.
     */
    public function employerShare(DeductionType $type, float $basic, float $gross): float
    {
        $percent = (float) $type->employer_share_percent;

        if ($percent <= 0) {
            return 0.0;
        }

        return match ($type->statutory_kind) {
            'pf' => $this->percentOf($this->capped($basic, $type), $percent),
            'esi' => $this->withinCeiling($gross, $type) ? $this->percentOf($gross, $percent) : 0.0,
            default => 0.0,
        };
    }

    private function capped(float $amount, DeductionType $type): float
    {
        $ceiling = $type->wage_ceiling === null ? null : (float) $type->wage_ceiling;

        return $ceiling === null ? $amount : min($amount, $ceiling);
    }

    private function withinCeiling(float $gross, DeductionType $type): bool
    {
        $ceiling = $type->wage_ceiling === null ? null : (float) $type->wage_ceiling;

        return $ceiling === null || $gross <= $ceiling;
    }

    private function percentOf(float $amount, float $percent): float
    {
        return round(max(0, $amount) * $percent / 100, 2);
    }
}
