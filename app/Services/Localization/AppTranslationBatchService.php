<?php

namespace App\Services\Localization;

use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Localization\AppTranslationBatchContract;
use App\Contracts\Localization\AppTranslationRepositoryContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Contracts\Localization\WebsiteTranslationLookupContract;
use App\Models\Communication\AppTranslation;
use Throwable;

/**
 * SRP: fill the next batch of missing app strings.
 *
 * Order per missing (app, key, language): the website's translation of the
 * same English text, then another app's, then Google. Copies cost nothing, so
 * they are all saved in one go; only Google calls are batched and time-boxed.
 * Existing values (including admin corrections) are never touched.
 */
final class AppTranslationBatchService implements AppTranslationBatchContract
{
    /** Stop a batch early when the translator keeps failing. */
    private const MAX_CONSECUTIVE_FAILURES = 3;

    /** Seconds of work per request, well under shared-hosting PHP limits. */
    private const TIME_BUDGET_SECONDS = 20;

    public function __construct(
        private readonly AppTranslationRepositoryContract $repository,
        private readonly WebsiteTranslationLookupContract $website,
        private readonly SupportedLocalesContract $locales,
        private readonly TextTranslatorContract $translator,
    ) {}

    public function translateNextBatch(?string $app): array
    {
        $default = $this->locales->default();
        $targets = $this->locales->translatable();
        $batchSize = max(1, (int) config('localization.app_translate_batch_size', 40));

        $index = $this->repository->translatedIndex(null, $targets);
        $fromWebsite = $this->websiteMap($targets);
        $fromApps = $this->appMap($index);
        $pending = $this->pending($this->repository->englishRows($app, $default), $targets, $index);

        $copies = [];
        $toTranslate = [];
        $counts = ['translated' => 0, 'website' => 0, 'reused' => 0, 'failed' => 0];

        foreach ($pending as [$row, $locale]) {
            $match = $this->matchKey($locale, $row['english']);

            if (! $this->hasPlaceholder($row['english']) && isset($fromWebsite[$match])) {
                $copies[] = $this->record($row, $locale, $fromWebsite[$match], AppTranslation::SOURCE_WEBSITE);
                $counts['website']++;
            } elseif (isset($fromApps[$match])) {
                $copies[] = $this->record($row, $locale, $fromApps[$match], AppTranslation::SOURCE_APP);
                $counts['reused']++;
            } else {
                $toTranslate[] = [$row, $locale];
            }
        }

        if ($copies !== []) {
            $this->repository->saveTranslations($copies);
        }

        $translated = [];
        $failuresInRow = 0;
        $startedAt = microtime(true);

        foreach (array_slice($toTranslate, 0, $batchSize) as [$row, $locale]) {
            if (microtime(true) - $startedAt > self::TIME_BUDGET_SECONDS) {
                break;
            }

            $match = $this->matchKey($locale, $row['english']);

            // Same English earlier in this batch: copy it instead of asking again.
            $value = $translated[$match] ?? $this->translate($row['english'], $default, $locale);

            if ($value === '') {
                $counts['failed']++;

                if (++$failuresInRow >= self::MAX_CONSECUTIVE_FAILURES) {
                    break;
                }

                continue;
            }

            $failuresInRow = 0;
            $source = isset($translated[$match]) ? AppTranslation::SOURCE_APP : AppTranslation::SOURCE_GOOGLE;
            $counts[$source === AppTranslation::SOURCE_APP ? 'reused' : 'translated']++;
            $translated[$match] = $value;
            $this->repository->saveTranslations([$this->record($row, $locale, $value, $source)]);
        }

        $done = $counts['translated'] + $counts['website'] + $counts['reused'];

        return $counts + ['remaining' => max(0, count($pending) - $done)];
    }

    private function translate(string $english, string $from, string $to): string
    {
        try {
            return trim($this->translator->translate($english, $from, $to));
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @param  array<int, array{app: string, key: string, english: string}>  $rows
     * @param  array<int, string>  $targets
     * @param  array<string, array{value: string, english: string}>  $index
     * @return array<int, array{0: array{app: string, key: string, english: string}, 1: string}>
     */
    private function pending(array $rows, array $targets, array $index): array
    {
        $pending = [];

        foreach ($rows as $row) {
            foreach ($targets as $locale) {
                if (! isset($index[$row['app'].'|'.$row['key'].'|'.$locale])) {
                    $pending[] = [$row, $locale];
                }
            }
        }

        return $pending;
    }

    /**
     * @param  array<int, string>  $targets
     * @return array<string, string> match key => website translation
     */
    private function websiteMap(array $targets): array
    {
        $map = [];

        foreach ($this->website->translationsFor($targets) as $row) {
            // An "auto-translation" identical to the English is a failed one.
            if (trim($row['value']) === '' || trim($row['value']) === trim($row['english'])) {
                continue;
            }

            $map[$this->matchKey($row['locale'], $row['english'])] ??= $row['value'];
        }

        return $map;
    }

    /**
     * @param  array<string, array{value: string, english: string}>  $index
     * @return array<string, string> match key => another app's translation
     */
    private function appMap(array $index): array
    {
        $map = [];

        foreach ($index as $compositeKey => $entry) {
            if ($entry['english'] === '') {
                continue;
            }

            $locale = substr($compositeKey, strrpos($compositeKey, '|') + 1);
            $map[$this->matchKey($locale, $entry['english'])] ??= $entry['value'];
        }

        return $map;
    }

    /** Same language + same English, ignoring case and extra spaces. */
    private function matchKey(string $locale, string $english): string
    {
        return $locale.'|'.mb_strtolower((string) preg_replace('/\s+/u', ' ', trim($english)));
    }

    /** App placeholders ({n}) are written differently on the website (:n). */
    private function hasPlaceholder(string $english): bool
    {
        return str_contains($english, '{');
    }

    /**
     * @param  array{app: string, key: string, english: string}  $row
     * @return array{app: string, key: string, locale: string, english: string, value: string, source: string}
     */
    private function record(array $row, string $locale, string $value, string $source): array
    {
        return ['app' => $row['app'], 'key' => $row['key'], 'locale' => $locale, 'english' => $row['english'], 'value' => $value, 'source' => $source];
    }
}
