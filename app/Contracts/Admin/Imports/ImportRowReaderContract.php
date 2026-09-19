<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: reading values out of a raw spreadsheet row.
 *
 * Headers arrive in whatever case and punctuation the person used, so every
 * lookup goes through the same normalisation.
 */
interface ImportRowReaderContract
{
    public function header(string $header): string;

    /**
     * First non-empty value among the given column names.
     *
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    public function firstFilled(array $row, array $keys): ?string;
}
