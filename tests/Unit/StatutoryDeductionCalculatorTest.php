<?php

namespace Tests\Unit;

use App\Models\Hr\DeductionType;
use App\Services\Hr\Payroll\StatutoryDeductionCalculator;
use Tests\TestCase;

/**
 * PF, ESI and Professional Tax do not follow the plain fixed/percentage rule,
 * so each legal quirk is pinned here: PF caps the wage it charges, ESI stops
 * applying once gross passes its ceiling, and PT is a flat slab.
 */
class StatutoryDeductionCalculatorTest extends TestCase
{
    private function type(array $attributes): DeductionType
    {
        return new DeductionType($attributes + [
            'name' => 'Test', 'code' => 'test', 'calculation_type' => 'fixed',
            'default_value' => 0, 'statutory_kind' => 'none', 'employer_share_percent' => 0,
        ]);
    }

    public function test_pf_is_twelve_percent_of_basic_below_the_ceiling(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $pf = $this->type(['statutory_kind' => 'pf', 'default_value' => 12, 'employer_share_percent' => 12, 'wage_ceiling' => 15000]);

        $this->assertSame(1200.0, $calculator->employeeShare($pf, 10000, 14000));
        $this->assertSame(1200.0, $calculator->employerShare($pf, 10000, 14000));
    }

    public function test_pf_charges_only_up_to_the_wage_ceiling(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $pf = $this->type(['statutory_kind' => 'pf', 'default_value' => 12, 'employer_share_percent' => 12, 'wage_ceiling' => 15000]);

        // Basic is 40,000 but PF is charged on 15,000 only.
        $this->assertSame(1800.0, $calculator->employeeShare($pf, 40000, 52000));
    }

    public function test_pf_without_a_ceiling_uses_the_whole_basic(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $pf = $this->type(['statutory_kind' => 'pf', 'default_value' => 12, 'wage_ceiling' => null]);

        $this->assertSame(4800.0, $calculator->employeeShare($pf, 40000, 52000));
    }

    public function test_esi_applies_on_gross_while_within_the_ceiling(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $esi = $this->type(['statutory_kind' => 'esi', 'default_value' => 0.75, 'employer_share_percent' => 3.25, 'wage_ceiling' => 21000]);

        $this->assertSame(150.0, $calculator->employeeShare($esi, 12000, 20000));
        $this->assertSame(650.0, $calculator->employerShare($esi, 12000, 20000));
    }

    public function test_esi_stops_once_gross_passes_the_ceiling(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $esi = $this->type(['statutory_kind' => 'esi', 'default_value' => 0.75, 'employer_share_percent' => 3.25, 'wage_ceiling' => 21000]);

        $this->assertSame(0.0, $calculator->employeeShare($esi, 18000, 25000));
        $this->assertSame(0.0, $calculator->employerShare($esi, 18000, 25000));
    }

    public function test_professional_tax_is_a_flat_amount_and_never_has_an_employer_share(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $pt = $this->type(['statutory_kind' => 'professional_tax', 'default_value' => 200, 'employer_share_percent' => 10]);

        $this->assertSame(200.0, $calculator->employeeShare($pt, 30000, 45000));
        $this->assertSame(0.0, $calculator->employerShare($pt, 30000, 45000));
    }

    public function test_professional_tax_is_not_charged_when_there_is_no_pay(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $pt = $this->type(['statutory_kind' => 'professional_tax', 'default_value' => 200]);

        $this->assertSame(0.0, $calculator->employeeShare($pt, 0, 0));
    }

    public function test_a_non_statutory_type_is_never_computed_here(): void
    {
        $calculator = new StatutoryDeductionCalculator;
        $other = $this->type(['statutory_kind' => 'none', 'default_value' => 500, 'employer_share_percent' => 5]);

        $this->assertSame(0.0, $calculator->employeeShare($other, 20000, 25000));
        $this->assertSame(0.0, $calculator->employerShare($other, 20000, 25000));
    }
}
