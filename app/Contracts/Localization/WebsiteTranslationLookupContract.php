<?php

namespace App\Contracts\Localization;

/**
 * Read-only access to the website's finished translations
 * (`web_translations`), so app strings with the same English text can reuse
 * them instead of being translated again.
 */
interface WebsiteTranslationLookupContract
{
    /**
     * Active, non-empty website translations for the given languages.
     *
     * @param  array<int, string>  $locales
     * @return array<int, array{locale: string, english: string, value: string}>
     */
    public function translationsFor(array $locales): array;
}
