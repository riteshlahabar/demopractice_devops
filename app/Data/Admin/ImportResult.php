<?php

namespace App\Data\Admin;

/**
 * Outcome of one import run, so the controller reports it without knowing how
 * the rows were processed.
 */
final class ImportResult
{
    public function __construct(
        public readonly int $created = 0,
        public readonly int $updated = 0,
        public readonly int $failed = 0,
        public readonly ?string $firstError = null,
    ) {}

    public function summary(): string
    {
        return "Import completed. Created: {$this->created}, Updated: {$this->updated}, Failed: {$this->failed}.";
    }

    public function hasFailures(): bool
    {
        return $this->failed > 0 && $this->firstError !== null;
    }
}
