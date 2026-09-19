<?php

namespace App\Contracts\Hr;

use Carbon\CarbonInterface;

interface IncentiveCalculationContract
{
    /**
     * Incentive earned in a month by matching the salesman's performance
     * against the active Incentive Rules.
     */
    public function incentiveFor(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float;

    /**
     * Commission earned in a month from the active Commission Rules.
     */
    public function commissionFor(int $salesmanId, CarbonInterface $monthStart, CarbonInterface $monthEnd): float;
}
