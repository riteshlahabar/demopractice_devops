<?php

namespace App\Contracts\Localization;

/**
 * Backs the admin Translate button: fills a small batch of missing app
 * translations per call, so the browser can loop until nothing is left.
 */
interface AppTranslationBatchContract
{
    /**
     * @return array{translated: int, website: int, reused: int, failed: int, remaining: int}
     */
    public function translateNextBatch(?string $app): array;
}
