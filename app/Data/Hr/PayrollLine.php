<?php

namespace App\Data\Hr;

/**
 * One computed allowance or deduction, ready to be written as a
 * `salary_slip_lines` row.
 */
final class PayrollLine
{
    public function __construct(
        public readonly string $kind,
        public readonly string $source,
        public readonly ?int $sourceId,
        public readonly string $label,
        public readonly float $amount,
    ) {}

    public static function allowance(string $source, ?int $sourceId, string $label, float $amount): self
    {
        return new self('allowance', $source, $sourceId, $label, round($amount, 2));
    }

    public static function deduction(string $source, ?int $sourceId, string $label, float $amount): self
    {
        return new self('deduction', $source, $sourceId, $label, round($amount, 2));
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(): array
    {
        return [
            'kind' => $this->kind,
            'source' => $this->source,
            'source_id' => $this->sourceId,
            'label' => $this->label,
            'amount' => $this->amount,
        ];
    }
}
