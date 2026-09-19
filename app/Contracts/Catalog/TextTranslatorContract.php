<?php

namespace App\Contracts\Catalog;

/**
 * ISP:
 * Small contract with only one translation responsibility.
 *
 * DIP:
 * High-level services depend on this abstraction instead of
 * Google/Firebase/HTTP implementation details.
 */
interface TextTranslatorContract
{
    public function translate(
        string $text,
        string $sourceLocale,
        string $targetLocale
    ): string;
}
