<?php

namespace App\Contracts\Localization;

/**
 * Storage for the mobile apps' UI strings (`app_translations`).
 */
interface AppTranslationRepositoryContract
{
    /**
     * English source text per key for one app (locale = default language).
     *
     * @return array<string, string>
     */
    public function englishFor(string $app, string $defaultLocale): array;

    /**
     * Upserts English rows. Keys whose English text changed have their other
     * language rows cleared so the next Translate run redoes them; rows an
     * admin corrected by hand are left alone.
     *
     * @param  array<string, string>  $items
     * @param  array<int, string>  $changedKeys
     */
    public function saveEnglish(string $app, string $defaultLocale, array $items, array $changedKeys): void;

    /**
     * Active, non-empty translations for one app and language.
     *
     * @return array<string, string>
     */
    public function translationsFor(string $app, string $locale): array;

    /**
     * Every English row, optionally for one app.
     *
     * @return array<int, array{app: string, key: string, english: string}>
     */
    public function englishRows(?string $app, string $defaultLocale): array;

    /**
     * Keys already translated, as "app|key|locale" => value, optionally for one app.
     *
     * @param  array<int, string>  $locales
     * @return array<string, array{value: string, english: string}>
     */
    public function translatedIndex(?string $app, array $locales): array;

    /**
     * Stores translated values in bulk without touching rows' model events.
     *
     * @param  array<int, array{app: string, key: string, locale: string, english: string, value: string, source: string}>  $rows
     */
    public function saveTranslations(array $rows): void;
}
