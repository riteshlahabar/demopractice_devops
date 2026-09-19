<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: deciding where an image named in a spreadsheet lives under public/.
 */
interface ImportImagePathContract
{
    /**
     * Returns an empty string for anything unusable - absolute URLs, traversal
     * attempts, or blanks.
     */
    public function normalize(string $path, string $module): string;

    /**
     * Splits a pipe or semicolon separated gallery cell into normalised paths.
     *
     * @return array<int, string>
     */
    public function galleryPaths(?string $value): array;
}
