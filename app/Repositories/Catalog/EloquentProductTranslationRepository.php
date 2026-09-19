<?php

namespace App\Repositories\Catalog;

use App\Contracts\Catalog\ProductTranslationRepositoryContract;
use App\Models\Catalog\ProductTranslation;

/**
 * SRP:
 * Contains ProductTranslation database operations only.
 */
class EloquentProductTranslationRepository implements ProductTranslationRepositoryContract
{
    public function getByProductId(int $productId): array
    {
        return ProductTranslation::query()
            ->where('product_id', $productId)
            ->get([
                'locale',
                'name',
                'description',
            ])
            ->mapWithKeys(
                fn (ProductTranslation $translation): array => [
                    $translation->locale => [
                        'name' => $translation->name,
                        'description' => $translation->description,
                    ],
                ]
            )
            ->all();
    }

    public function deleteLocale(
        int $productId,
        string $locale
    ): void {
        ProductTranslation::query()
            ->where('product_id', $productId)
            ->where('locale', $locale)
            ->delete();
    }

    public function upsert(
        int $productId,
        string $locale,
        string $name,
        ?string $description
    ): void {
        ProductTranslation::query()->updateOrCreate(
            [
                'product_id' => $productId,
                'locale' => $locale,
            ],
            [
                'name' => $name,
                'description' => $description,
            ]
        );
    }
}
