<?php

namespace App\Contracts\Hr;

use App\Data\Hr\PayrollResult;
use App\Models\User;

interface PayrollComputationContract
{
    /**
     * Work out one salesman's pay for one month: basic, every allowance and
     * deduction that applies, statutory contributions and advance recovery.
     */
    public function compute(User $salesman, int $year, int $month): PayrollResult;
}
