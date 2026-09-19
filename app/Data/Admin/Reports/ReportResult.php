<?php

namespace App\Data\Admin\Reports;

/**
 * What a report produces: summary cards on top and one table below.
 *
 * Card:   ['label' => string, 'value' => mixed, 'type' => money|number|percent|text]
 * Column: ['key' => string, 'label' => string, 'type' => text|money|number|percent|date|status]
 */
final readonly class ReportResult
{
    /**
     * @param  array<int, array{label: string, value: mixed, type?: string}>  $cards
     * @param  array<int, array{key: string, label: string, type?: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        public array $cards,
        public array $columns,
        public array $rows,
        public ?string $note = null,
    ) {}
}
