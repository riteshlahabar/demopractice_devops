<?php

namespace App\Contracts\Admin\People;

use App\Data\Admin\People\PersonSummary;
use App\Models\User;

/**
 * Builds the stat tiles, role details and recent-records table shown on the
 * admin Dealer / Customer / Salesman view page.
 */
interface PersonSummaryContract
{
    public function for(User $person): PersonSummary;
}
