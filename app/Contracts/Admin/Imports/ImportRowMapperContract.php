<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: mapping a spreadsheet row onto model attributes, and saying how that
 * row identifies an existing record.
 */
interface ImportRowMapperContract
{
    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $fields  Field names from the module config.
     * @return array<string, mixed>
     */
    public function map(array $row, array $fields, string $module): array;

    /**
     * Columns that decide update-or-create. An empty array means always create.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function uniqueKeysFor(array $data, string $module): array;
}
