<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductSkuContract;
use App\Models\Catalog\Product;
use Illuminate\Support\Str;

final class ProductSkuService implements ProductSkuContract
{
    private const FALLBACK_PREFIX = 'PRD';

    public function generate(?string $productName = null): string
    {
        $prefix = $this->prefix($productName);
        $sequence = ((int) Product::query()->max('id')) + 1;

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $candidate = $prefix.'-'.str_pad((string) ($sequence + $attempt), 5, '0', STR_PAD_LEFT);

            if (! Product::query()->where('sku', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $prefix.'-'.strtoupper(Str::random(8));
    }

    /**
     * First letters of the product name keep the code readable in listings and
     * on invoices; anything unusable falls back to a fixed prefix.
     */
    private function prefix(?string $productName): string
    {
        $letters = strtoupper((string) preg_replace('/[^A-Za-z]/', '', (string) $productName));

        return $letters === '' ? self::FALLBACK_PREFIX : substr($letters, 0, 3);
    }
}
