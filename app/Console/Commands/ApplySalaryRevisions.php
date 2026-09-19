<?php

namespace App\Console\Commands;

use App\Contracts\Hr\SalaryRevisionContract;
use Illuminate\Console\Command;

/**
 * A revision can be recorded with a future date; this writes it to the employee
 * record on the day it takes effect. Safe to run daily — an already applied
 * revision is stamped and never picked up twice.
 */
class ApplySalaryRevisions extends Command
{
    protected $signature = 'salary:apply-revisions';

    protected $description = 'Write salary revisions that have come due onto the employee records.';

    public function handle(SalaryRevisionContract $revisions): int
    {
        $applied = $revisions->applyDue();

        $this->info($applied === 0
            ? 'No salary revisions were due.'
            : "Applied {$applied} salary revision(s).");

        return self::SUCCESS;
    }
}
