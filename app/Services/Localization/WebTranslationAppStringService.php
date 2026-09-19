<?php

namespace App\Services\Localization;

use App\Contracts\Localization\AppStringTranslationContract;
use App\Contracts\Localization\SupportedLocalesContract;

/**
 * SRP: run the apps' English labels through the same web_t() pipeline the
 * storefront uses, so a string translated for the website is reused by the
 * apps and vice versa.
 */
final class WebTranslationAppStringService implements AppStringTranslationContract
{
    public function __construct(private readonly SupportedLocalesContract $locales) {}

    public function translate(string $locale, array $items): array
    {
        if (! $this->locales->isSupported($locale) || $locale === $this->locales->default()) {
            // English (or an unknown locale) needs no lookup; the app already
            // has the text it sent.
            return array_map(static fn ($text): string => (string) $text, $items);
        }

        $max = (int) config('localization.app_sync_max_items', 400);
        $translated = [];

        foreach (array_slice($items, 0, $max, true) as $key => $english) {
            $english = trim((string) $english);

            if ($key === '' || $english === '') {
                continue;
            }

            $translated[$key] = web_t($key, $english, [], $locale);
        }

        return $translated;
    }
}
