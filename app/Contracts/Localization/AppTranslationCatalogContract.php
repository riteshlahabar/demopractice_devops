<?php

namespace App\Contracts\Localization;

/**
 * What the mobile apps call: register their English strings, then read the
 * finished translations. Neither call translates anything, so both are fast.
 */
interface AppTranslationCatalogContract
{
    /**
     * @param  array<string, string>  $items  key => English text
     * @return int number of keys stored
     */
    public function register(string $app, array $items): int;

    /**
     * @return array<string, string>
     */
    public function translations(string $app, string $locale): array;
}
