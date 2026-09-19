<?php

namespace Tests\Unit;

use App\Data\Hr\PayrollLine;
use App\Data\Hr\PayrollResult;
use App\Models\Hr\AllowanceType;
use App\Models\Hr\HrmsSetting;
use Tests\TestCase;

class PayrollResultTest extends TestCase
{
    private function payroll(float $basic, float $allowances, float $deductions): PayrollResult
    {
        return new PayrollResult(
            basicSalary: $basic,
            allowances: $allowances,
            deductions: $deductions,
            employerContribution: 0,
            workingDays: 30,
            payableDays: 30,
            lines: [
                PayrollLine::allowance('allowance_type', 1, 'Travel', $allowances),
                PayrollLine::deduction('deduction_type', 2, 'PF', $deductions),
            ],
        );
    }

    public function test_gross_is_basic_plus_allowances_and_net_takes_deductions_off(): void
    {
        $result = $this->payroll(20000, 3500, 2400);

        $this->assertSame(23500.0, $result->grossSalary());
        $this->assertSame(21100.0, $result->netSalary());
    }

    public function test_net_salary_never_goes_below_zero(): void
    {
        $this->assertSame(0.0, $this->payroll(5000, 0, 9000)->netSalary());
    }

    public function test_lines_can_be_read_back_by_kind(): void
    {
        $result = $this->payroll(20000, 3500, 2400);

        $this->assertCount(1, $result->linesOf('allowance'));
        $this->assertCount(1, $result->linesOf('deduction'));
        $this->assertSame('Travel', $result->linesOf('allowance')[0]->label);
    }

    public function test_slip_columns_carry_every_total(): void
    {
        $columns = $this->payroll(20000, 3500, 2400)->toSlipColumns();

        $this->assertSame(20000.0, $columns['basic_salary']);
        $this->assertSame(3500.0, $columns['allowances']);
        $this->assertSame(23500.0, $columns['gross_salary']);
        $this->assertSame(2400.0, $columns['deductions']);
        $this->assertSame(21100.0, $columns['net_salary']);
        $this->assertSame(30.0, $columns['working_days']);
    }

    public function test_an_allowance_type_computes_fixed_and_percentage_amounts(): void
    {
        $fixed = new AllowanceType(['name' => 'Mobile', 'code' => 'mobile', 'calculation_type' => 'fixed', 'default_value' => 800]);
        $percent = new AllowanceType(['name' => 'DA', 'code' => 'da', 'calculation_type' => 'percent_of_basic', 'default_value' => 10]);

        $this->assertSame(800.0, $fixed->amountFor(20000));
        $this->assertSame(2000.0, $percent->amountFor(20000));
        // A per-employee row may override both the rule and the value.
        $this->assertSame(1500.0, $fixed->amountFor(20000, 'fixed', 1500));
        $this->assertSame(1000.0, $percent->amountFor(20000, 'percent_of_basic', 5));
    }

    public function test_hrms_settings_defaults_survive_an_unsaved_row(): void
    {
        $settings = new HrmsSetting;

        $this->assertSame(10, $settings->grace_minutes);
        $this->assertSame('calendar', $settings->working_days_basis);
        $this->assertTrue($settings->deduct_absent_days);
        $this->assertSame(['sunday'], $settings->default_weekly_offs);
    }

    public function test_hrms_settings_round_net_pay_the_configured_way(): void
    {
        $nearest = (new HrmsSetting)->forceFill(['rounding_mode' => 'nearest']);
        $up = (new HrmsSetting)->forceFill(['rounding_mode' => 'up']);
        $down = (new HrmsSetting)->forceFill(['rounding_mode' => 'down']);
        $none = (new HrmsSetting)->forceFill(['rounding_mode' => 'none']);

        $this->assertSame(21100.0, $nearest->round(21099.60));
        $this->assertSame(21100.0, $up->round(21099.10));
        $this->assertSame(21099.0, $down->round(21099.90));
        $this->assertSame(21099.61, $none->round(21099.607));
    }
}
