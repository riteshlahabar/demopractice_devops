<?php

namespace App\Data\Admin\People;

/**
 * Everything role-specific on the person view page, already formatted.
 *
 * tiles:   [label, value, icon (feather), tone]
 * details: [label => value]
 * table:   rows of [column key => text] plus an optional `url` for the first cell
 */
final readonly class PersonSummary
{
    /**
     * @param  array<int, array{label: string, value: string, icon: string, tone: string}>  $tiles
     * @param  array<string, string>  $details
     * @param  array<int, array{key: string, label: string, align?: string, status?: bool}>  $tableColumns
     * @param  array<int, array<string, string|null>>  $tableRows
     */
    public function __construct(
        public string $code,
        public string $detailsTitle,
        public array $tiles,
        public array $details,
        public string $tableTitle,
        public array $tableColumns,
        public array $tableRows,
        public string $tableEmpty,
    ) {}

    public static function empty(): self
    {
        return new self('', 'Details', [], [], '', [], [], '');
    }
}
