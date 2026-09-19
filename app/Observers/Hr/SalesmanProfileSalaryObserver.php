<?php

namespace App\Observers\Hr;

use App\Contracts\Hr\SalaryRevisionContract;
use App\Models\SalesmanProfile;
use Throwable;

/**
 * A salary changed on the employee form is still a revision, so it is written
 * to the history automatically rather than relying on someone also filling in
 * the Salary Revisions screen.
 */
final class SalesmanProfileSalaryObserver
{
    public function __construct(
        private readonly SalaryRevisionContract $revisions
    ) {}

    public function updated(SalesmanProfile $profile): void
    {
        if (! $profile->wasChanged('basic_salary')) {
            return;
        }

        $this->write($profile, $profile->getOriginal('basic_salary'));
    }

    /** The first salary is the start of the history, not a revision of it. */
    public function created(SalesmanProfile $profile): void
    {
        if ((float) $profile->basic_salary > 0) {
            $this->write($profile, null);
        }
    }

    private function write(SalesmanProfile $profile, mixed $previous): void
    {
        try {
            $this->revisions->record(
                salesmanId: (int) $profile->user_id,
                previousBasic: $previous === null ? null : (float) $previous,
                newBasic: (float) $profile->basic_salary,
                reason: $previous === null ? 'other' : 'increment',
                notes: $previous === null ? 'Opening salary on the employee record.' : 'Changed on the employee record.',
                source: 'profile',
            );
        } catch (Throwable) {
            // History is a convenience; it may never block saving the employee.
        }
    }
}
