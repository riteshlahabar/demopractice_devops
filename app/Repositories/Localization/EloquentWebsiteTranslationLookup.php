<?php

namespace App\Repositories\Localization;

use App\Contracts\Localization\WebsiteTranslationLookupContract;
use App\Models\Communication\WebTranslation;

final class EloquentWebsiteTranslationLookup implements WebsiteTranslationLookupContract
{
    public function translationsFor(array $locales): array
    {
        if ($locales === []) {
            return [];
        }

        $rows = [];

        WebTranslation::query()
            ->whereIn('locale', $locales)
            ->where('is_active', true)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->select(['id', 'locale', 'english_text', 'value'])
            ->chunkById(1000, function ($chunk) use (&$rows): void {
                foreach ($chunk as $row) {
                    $rows[] = [
                        'locale' => (string) $row->locale,
                        'english' => (string) $row->english_text,
                        'value' => mb_convert_encoding((string) $row->value, 'UTF-8', 'UTF-8'),
                    ];
                }
            });

        return $rows;
    }
}
