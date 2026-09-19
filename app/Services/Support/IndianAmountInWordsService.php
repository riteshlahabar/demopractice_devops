<?php

namespace App\Services\Support;

use App\Contracts\Support\AmountInWordsContract;

/**
 * Indian numbering (thousand, lakh, crore), with paise when present.
 */
final class IndianAmountInWordsService implements AmountInWordsContract
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public function rupees(float $amount): string
    {
        $paiseTotal = (int) round(abs($amount) * 100);
        $rupees = intdiv($paiseTotal, 100);
        $paise = $paiseTotal % 100;

        $words = $rupees === 0 ? 'Zero' : $this->number($rupees);

        if ($paise > 0) {
            $words .= ' and '.$this->belowHundred($paise).' Paise';
        }

        return 'Rs. '.$words.' Only';
    }

    private function number(int $value): string
    {
        $parts = [];

        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $size => $label) {
            if ($value >= $size) {
                // Crores can exceed 99, so they are spelled recursively.
                $count = intdiv($value, $size);
                $parts[] = ($size === 10000000 ? $this->number($count) : $this->belowHundred($count)).' '.$label;
                $value %= $size;
            }
        }

        if ($value > 0) {
            $parts[] = $this->belowHundred($value);
        }

        return implode(' ', $parts);
    }

    private function belowHundred(int $value): string
    {
        if ($value < 20) {
            return self::ONES[$value];
        }

        return trim(self::TENS[intdiv($value, 10)].' '.self::ONES[$value % 10]);
    }
}
