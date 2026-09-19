<?php

namespace App\Contracts\Catalog;

/**
 * ISP:
 * Focused persistence contract only for Product translations.
 *
 * DIP:
 * ProductTranslationService does not depend directly on Eloquent.
 */
interface ProductTranslationRepositoryContract
{
    public function getByProductId(int $productId): array;

    public function deleteLocale(
        int $productId,
        string $locale
    ): void;

    public function upsert(
        int $productId,
        string $locale,
        string $name,
        ?string $description
    ): void;
}
