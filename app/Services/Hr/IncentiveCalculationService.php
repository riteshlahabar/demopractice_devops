<?php

namespace App\Services\Hr;

use App\Contracts\Hr\IncentiveCalculationContract;
use App\Models\Field\DealerVisit;
use App\Models\Field\SalesmanTarget;
use App\Models\Finance\Payment;
use App\Models\Hr\CommissionRule;
use App\Models\Hr\IncentiveRule;
use App\Models\Sales\Order;
use App\Models\SalesmanProfile;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Turns the Incentive and Commission Rules masters into two numbers for a
 * payroll month.
 *
 * Only the **highest matching incentive slab** is paid — slabs describe bands
 * of the same measure, so paying every slab a salesman passed through would
 * pay the same achievement several times over. Commission rules are the
 * opposite: they describe different products and categories, so every matching
 * rule is paid and the results add up.
 */
class IncentiveCalculationService implements IncentiveCalculationContract
{
    public function incentiveFor(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        $rules = $this->applicableIncentiveRules($salesmanId, $monthEnd);

        if ($rules->isEmpty()) {
            return 0.0;
        }

        $best = 0.0;

        foreach ($rules->groupBy('basis') as $basis => $basisRules) {
            [$measured, $base] = $this->measure($salesmanId, (string) $basis, $monthStart, $monthEnd);

            $matching = $basisRules
                ->filter(static fn (IncentiveRule $rule): bool => $rule->coversValue($measured))
                ->sortByDesc(static fn (IncentiveRule $rule): float => (float) $rule->slab_from)
                ->first();

            if ($matching !== null) {
                $best += $matching->rewardFor($measured, $base);
            }
        }

        return round($best, 2);
    }

    public function commissionFor(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        $rules = CommissionRule::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $monthEnd))
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $monthStart))
            ->where(fn ($query) => $query->where('applies_to', 'all')->orWhere('salesman_id', $salesmanId))
            ->orderBy('sort_order')
            ->get();

        $total = 0.0;

        foreach ($rules as $rule) {
            $total += $rule->commissionFor($this->salesValueFor($rule, $salesmanId, $monthStart, $monthEnd));
        }

        return round($total, 2);
    }

    /**
     * @return Collection<int, IncentiveRule>
     */
    private function applicableIncentiveRules(int $salesmanId, CarbonInterface $monthEnd): Collection
    {
        $profile = SalesmanProfile::query()->where('user_id', $salesmanId)->first();

        return IncentiveRule::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $monthEnd))
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $monthEnd->copy()->startOfMonth()))
            ->where(function ($query) use ($salesmanId, $profile): void {
                $query->where('applies_to', 'all')
                    ->orWhere(fn ($inner) => $inner->where('applies_to', 'salesman')->where('salesman_id', $salesmanId))
                    ->orWhere(fn ($inner) => $inner->where('applies_to', 'department')->where('department_id', $profile?->department_id))
                    ->orWhere(fn ($inner) => $inner->where('applies_to', 'designation')->where('designation_id', $profile?->designation_id));
            })
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * The value a rule is measured against, and the amount a percentage reward
     * is taken from. For target achievement the measure is a percentage but
     * the reward is paid on the sales actually achieved.
     *
     * @return array{0: float, 1: float}
     */
    private function measure(int $salesmanId, string $basis, CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        return match ($basis) {
            'target_achievement' => $this->targetAchievement($salesmanId, $monthStart, $monthEnd),
            'collection_value' => $this->repeat($this->collectionValue($salesmanId, $monthStart, $monthEnd)),
            'dealer_visits' => [(float) $this->visitCount($salesmanId, $monthStart, $monthEnd), (float) $this->visitCount($salesmanId, $monthStart, $monthEnd)],
            default => $this->repeat($this->orderValue($salesmanId, $monthStart, $monthEnd)),
        };
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function repeat(float $value): array
    {
        return [$value, $value];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function targetAchievement(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        $target = SalesmanTarget::query()
            ->where('salesman_id', $salesmanId)
            ->whereDate('period_start', '<=', $monthEnd)
            ->whereDate('period_end', '>=', $monthStart)
            ->orderByDesc('period_start')
            ->first();

        $achieved = $target !== null && (float) $target->achieved_amount > 0
            ? (float) $target->achieved_amount
            : $this->orderValue($salesmanId, $monthStart, $monthEnd);

        $goal = (float) ($target->target_amount ?? 0);

        if ($goal <= 0) {
            // With no target set there is no achievement percentage to band on.
            return [0.0, $achieved];
        }

        return [round($achieved / $goal * 100, 2), $achieved];
    }

    private function orderValue(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        return (float) Order::query()
            ->where('salesman_id', $salesmanId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
            ->sum('grand_total');
    }

    private function collectionValue(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        return (float) Payment::query()
            ->where('collected_by', $salesmanId)
            ->where('status', 'success')
            ->whereBetween('paid_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
            ->sum('amount');
    }

    private function visitCount(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): int
    {
        return DealerVisit::query()
            ->where('salesman_id', $salesmanId)
            ->whereBetween('created_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
            ->count();
    }

    private function salesValueFor(CommissionRule $rule, int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float
    {
        $orders = Order::query()
            ->where('salesman_id', $salesmanId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()]);

        if ($rule->basis === 'sales_value') {
            return (float) $orders->sum('grand_total');
        }

        // Product and category rules are paid on the matching order lines only.
        return (float) $orders
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->when(
                $rule->basis === 'product',
                fn ($query) => $query->where('order_items.product_id', $rule->product_id),
                fn ($query) => $query
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('products.category_id', $rule->category_id)
            )
            ->sum('order_items.line_total');
    }
}
