<?php

namespace Tests\Unit;

use App\Models\Hr\CommissionRule;
use App\Models\Hr\IncentiveRule;
use App\Models\Hr\Resignation;
use App\Models\Hr\Task;
use Tests\TestCase;

class IncentiveAndCommissionRuleTest extends TestCase
{
    private function incentive(array $attributes): IncentiveRule
    {
        return new IncentiveRule($attributes + [
            'name' => 'Slab', 'basis' => 'target_achievement', 'slab_from' => 0,
            'reward_type' => 'percent', 'reward_value' => 0, 'applies_to' => 'all',
        ]);
    }

    public function test_a_slab_covers_values_between_its_bounds(): void
    {
        $rule = $this->incentive(['slab_from' => 80, 'slab_to' => 100]);

        $this->assertFalse($rule->coversValue(79.99));
        $this->assertTrue($rule->coversValue(80));
        $this->assertTrue($rule->coversValue(100));
        $this->assertFalse($rule->coversValue(100.01));
    }

    public function test_a_slab_with_no_upper_bound_is_open_ended(): void
    {
        $rule = $this->incentive(['slab_from' => 120, 'slab_to' => null]);

        $this->assertFalse($rule->coversValue(119));
        $this->assertTrue($rule->coversValue(500000));
    }

    public function test_percentage_reward_is_taken_from_the_base_amount_not_the_measure(): void
    {
        // Measured on 110% achievement, but paid as 2% of the sales achieved.
        $rule = $this->incentive(['slab_from' => 100, 'reward_type' => 'percent', 'reward_value' => 2]);

        $this->assertSame(4400.0, $rule->rewardFor(110, 220000));
    }

    public function test_fixed_reward_ignores_the_base_amount(): void
    {
        $rule = $this->incentive(['reward_type' => 'fixed', 'reward_value' => 5000]);

        $this->assertSame(5000.0, $rule->rewardFor(150, 900000));
    }

    public function test_reward_is_capped_by_the_maximum(): void
    {
        $rule = $this->incentive(['reward_type' => 'percent', 'reward_value' => 5, 'max_reward' => 10000]);

        $this->assertSame(10000.0, $rule->rewardFor(130, 500000));
    }

    public function test_commission_is_zero_below_the_minimum_sales_value(): void
    {
        $rule = new CommissionRule(['name' => 'Base', 'basis' => 'sales_value', 'commission_percent' => 2, 'min_sales_value' => 100000]);

        $this->assertSame(0.0, $rule->commissionFor(99999));
        $this->assertSame(2000.0, $rule->commissionFor(100000));
    }

    public function test_commission_is_capped_by_the_maximum(): void
    {
        $rule = new CommissionRule(['name' => 'Base', 'basis' => 'sales_value', 'commission_percent' => 5, 'min_sales_value' => 0, 'max_commission' => 7500]);

        $this->assertSame(7500.0, $rule->commissionFor(400000));
    }

    public function test_settlement_is_dues_minus_recoveries_and_never_negative(): void
    {
        $resignation = new Resignation([
            'pending_salary' => 22000, 'leave_encashment' => 4000, 'other_dues' => 1000,
            'advance_recovery' => 9000, 'other_recovery' => 500,
        ]);

        $this->assertSame(17500.0, $resignation->computedSettlement());

        $owing = new Resignation(['pending_salary' => 1000, 'advance_recovery' => 9000]);
        $this->assertSame(0.0, $owing->computedSettlement());
    }

    public function test_notice_period_sets_the_last_working_date_unless_waived(): void
    {
        $served = new Resignation(['resignation_date' => '2026-09-01', 'notice_period_days' => 30]);
        $this->assertSame('2026-10-01', $served->noticePeriodEndsOn());

        $waived = new Resignation(['resignation_date' => '2026-09-01', 'notice_period_days' => 30, 'notice_period_waived' => true]);
        $this->assertSame('2026-09-01', $waived->noticePeriodEndsOn());
    }

    public function test_a_closed_task_is_never_overdue_however_old(): void
    {
        $open = new Task(['title' => 'Visit dealer', 'status' => 'pending', 'due_date' => '2020-01-01']);
        $done = new Task(['title' => 'Visit dealer', 'status' => 'completed', 'due_date' => '2020-01-01']);
        $cancelled = new Task(['title' => 'Visit dealer', 'status' => 'cancelled', 'due_date' => '2020-01-01']);
        $undated = new Task(['title' => 'Visit dealer', 'status' => 'pending']);

        $this->assertTrue($open->isOverdue());
        $this->assertFalse($done->isOverdue());
        $this->assertFalse($cancelled->isOverdue());
        $this->assertFalse($undated->isOverdue());
    }
}
