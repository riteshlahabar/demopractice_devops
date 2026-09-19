<?php

namespace App\Contracts\Localization;

/**
 * The single source of truth for which languages the system supports.
 *
 * Before this existed the locale list was hardcoded in three different places
 * (the languages table, the product translator and the profile service) and
 * they had drifted apart, so products were being translated into a language no
 * user could select.
 */
interface SupportedLocalesContract
{
    /**
     * Every active locale code, default first.
     *
     * @return array<int, string>
     */
    public function codes(): array;

    /**
     * Active locales excluding the default, i.e. the ones needing translation.
     *
     * @return array<int, string>
     */
    public function translatable(): array;

    public function isSupported(string $locale): bool;

    public function default(): string;
}
