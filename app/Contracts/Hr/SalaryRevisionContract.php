<?php

namespace App\Contracts\Hr;

use App\Models\Hr\SalaryRevision;

/**
 * Keeps the history of an employee's basic salary, and applies a revision to
 * the employee record when it takes effect.
 */
interface SalaryRevisionContract
{
    /**
     * Record a change of basic salary. Returns null when nothing actually
     * changed, and must never throw — losing the history entry may not stop
     * the salary change itself.
     */
    public function record(
        int $salesmanId,
        ?float $previousBasic,
        float $newBasic,
        ?string $effectiveFrom = null,
        string $reason = 'increment',
        ?string $notes = null,
        string $source = 'manual',
    ): ?SalaryRevision;

    /**
     * Write the revised amount onto the employee record, unless the revision
     * is dated in the future. Recording is suppressed so the same change is
     * not written to the history twice.
     */
    public function apply(SalaryRevision $revision): void;

    /**
     * Apply every revision that has come due and is not applied yet.
     *
     * @return int number of revisions applied
     */
    public function applyDue(): int;

    /**
     * Run a callback with recording switched off.
     */
    public function withoutRecording(callable $callback): mixed;
}
