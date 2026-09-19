<?php

namespace App\Services\Hr;

use App\Contracts\Hr\SalaryRevisionContract;
use App\Models\Hr\SalaryRevision;
use App\Models\SalesmanProfile;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Bound as a singleton so `withoutRecording()` is visible to the observer that
 * watches the employee record.
 */
final class SalaryRevisionService implements SalaryRevisionContract
{
    private bool $recording = true;

    public function record(
        int $salesmanId,
        ?float $previousBasic,
        float $newBasic,
        ?string $effectiveFrom = null,
        string $reason = 'increment',
        ?string $notes = null,
        string $source = 'manual',
    ): ?SalaryRevision {
        // Nothing changed, so there is nothing to explain later.
        if (! $this->recording || ($previousBasic !== null && round($previousBasic, 2) === round($newBasic, 2))) {
            return null;
        }

        try {
            return SalaryRevision::create([
                'salesman_id' => $salesmanId,
                'previous_basic' => $previousBasic,
                'new_basic' => $newBasic,
                'effective_from' => $effectiveFrom ?: now()->toDateString(),
                'reason' => array_key_exists($reason, SalaryRevision::REASONS) ? $reason : 'other',
                'notes' => $notes,
                'source' => $source,
                'revised_by' => auth()->id(),
                // Recorded from a change that has already happened.
                'applied_at' => $source === 'profile' ? now() : null,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Salary revision not recorded: '.$exception->getMessage());

            return null;
        }
    }

    public function apply(SalaryRevision $revision): void
    {
        // A revision dated ahead of today is a decision, not yet a salary;
        // `salary:apply-revisions` picks it up on the day it comes due.
        if ($revision->effective_from?->isFuture()) {
            return;
        }

        $this->withoutRecording(function () use ($revision): void {
            SalesmanProfile::query()
                ->where('user_id', $revision->salesman_id)
                ->update(['basic_salary' => $revision->new_basic]);

            $revision->forceFill(['applied_at' => now()])->saveQuietly();
        });
    }

    /**
     * Revisions that have come due since the last run, oldest first so a
     * salesman with two of them ends on the newest amount.
     *
     * @return int number of revisions applied
     */
    public function applyDue(): int
    {
        $due = SalaryRevision::query()
            ->whereNull('applied_at')
            ->whereDate('effective_from', '<=', now()->toDateString())
            ->orderBy('effective_from')
            ->get();

        foreach ($due as $revision) {
            $this->apply($revision);
        }

        return $due->count();
    }

    public function withoutRecording(callable $callback): mixed
    {
        $previous = $this->recording;
        $this->recording = false;

        try {
            return $callback();
        } finally {
            $this->recording = $previous;
        }
    }
}
