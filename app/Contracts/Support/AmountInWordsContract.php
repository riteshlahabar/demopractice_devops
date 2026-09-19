<?php

namespace App\Contracts\Support;

/**
 * Spells a rupee amount out the way Indian GST documents print it,
 * e.g. "Rs. One Lakh Twenty Thousand Only".
 */
interface AmountInWordsContract
{
    public function rupees(float $amount): string;
}
