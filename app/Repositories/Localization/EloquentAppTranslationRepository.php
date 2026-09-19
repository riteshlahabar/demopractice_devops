<?php

namespace App\Repositories\Localization;

use App\Contracts\Localization\AppTranslationRepositoryContract;
use App\Models\Communication\AppTranslation;

final class EloquentAppTranslationRepository implements AppTranslationRepositoryContract
{
    public function englishFor(string $app, string $defaultLocale): array
    {
        return AppTranslation::query()
            ->where('app', $app)
            ->where('locale', $defaultLocale)
            ->pluck('english_text', 'translation_key')
            ->map(fn ($text): string => (string) $text)
            ->all();
    }

    public function saveEnglish(string $app, string $defaultLocale, array $items, array $changedKeys): void
    {
        $now = now();
        $rows = [];

        foreach ($items as $key => $english) {
            $rows[] = [
                'app' => $app,
                'group' => explode('.', (string) $key, 2)[0],
                'translation_key' => (string) $key,
                'english_text' => $english,
                'locale' => $defaultLocale,
                'value' => $english,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            AppTranslation::query()->upsert(
                $chunk,
                ['app', 'translation_key', 'locale'],
                ['group', 'english_text', 'value', 'updated_at']
            );
        }

        if ($changedKeys !== []) {
            AppTranslation::query()
                ->where('app', $app)
                ->where('locale', '!=', $defaultLocale)
                ->whereIn('translation_key', $changedKeys)
                ->where(fn ($query) => $query->whereNull('source')->orWhere('source', '!=', AppTranslation::SOURCE_MANUAL))
                ->update(['value' => null, 'source' => null, 'updated_at' => $now]);
        }
    }

    public function translationsFor(string $app, string $locale): array
    {
        return AppTranslation::query()
            ->where('app', $app)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->pluck('value', 'translation_key')
            // A malformed byte in one row must never break the whole response.
            ->map(fn ($value): string => mb_convert_encoding((string) $value, 'UTF-8', 'UTF-8'))
            ->all();
    }

    public function englishRows(?string $app, string $defaultLocale): array
    {
        return AppTranslation::query()
            ->whereNotNull('app')
            ->when($app, fn ($query) => $query->where('app', $app))
            ->where('locale', $defaultLocale)
            ->whereNotNull('english_text')
            ->orderBy('id')
            ->get(['app', 'translation_key', 'english_text'])
            ->map(fn (AppTranslation $row): array => [
                'app' => (string) $row->app,
                'key' => (string) $row->translation_key,
                'english' => (string) $row->english_text,
            ])
            ->all();
    }

    public function translatedIndex(?string $app, array $locales): array
    {
        $index = [];

        AppTranslation::query()
            ->whereNotNull('app')
            ->when($app, fn ($query) => $query->where('app', $app))
            ->whereIn('locale', $locales)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->select(['id', 'app', 'translation_key', 'locale', 'english_text', 'value'])
            ->chunkById(1000, function ($rows) use (&$index): void {
                foreach ($rows as $row) {
                    $index[$row->app.'|'.$row->translation_key.'|'.$row->locale] = [
                        'value' => (string) $row->value,
                        'english' => (string) $row->english_text,
                    ];
                }
            });

        return $index;
    }

    public function saveTranslations(array $rows): void
    {
        $now = now();
        $records = array_map(fn (array $row): array => [
            'app' => $row['app'],
            'group' => explode('.', $row['key'], 2)[0],
            'translation_key' => $row['key'],
            'english_text' => $row['english'],
            'locale' => $row['locale'],
            'value' => $row['value'],
            'source' => $row['source'],
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows);

        foreach (array_chunk($records, 200) as $chunk) {
            AppTranslation::query()->upsert(
                $chunk,
                ['app', 'translation_key', 'locale'],
                ['group', 'english_text', 'value', 'source', 'updated_at']
            );
        }
    }
}
