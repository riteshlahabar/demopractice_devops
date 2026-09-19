<?php

namespace App\Contracts\Localization;

/**
 * Translates the mobile apps' own UI labels.
 *
 * The apps hold the English text, so they post it once per locale and the
 * server stores the result next to the website's strings — one table, one
 * admin screen, one translation provider to swap later.
 */
interface AppStringTranslationContract
{
    /**
     * @param  array<string, string>  $items  translation key => English text
     * @return array<string, string> translation key => translated text
     */
    public function translate(string $locale, array $items): array;
}
