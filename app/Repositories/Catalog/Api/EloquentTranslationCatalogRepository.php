<?php

namespace App\Repositories\Catalog\Api;

use App\Contracts\Catalog\Api\Repositories\TranslationCatalogRepositoryContract;
use App\Models\Communication\WebTranslation;
use Illuminate\Support\Collection;

/**
 * App UI strings now come from the same web_translations table the storefront
 * uses, so a phrase translated once is shared by the website and all three
 * apps. The old app_translations table is no longer read.
 */
final class EloquentTranslationCatalogRepository implements TranslationCatalogRepositoryContract
{
    public function activeForLocale(string $locale): Collection
    {
        return WebTranslation::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->pluck('value', 'translation_key');
    }
}
