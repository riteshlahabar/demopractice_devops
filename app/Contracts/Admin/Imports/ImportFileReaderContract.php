<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: turning an uploaded CSV or XLSX file into rows of raw cell values.
 */
interface ImportFileReaderContract
{
    /**
     * @return array<int, array<int, string|null>> First row is the header row.
     */
    public function rows(string $path, string $extension): array;
}
