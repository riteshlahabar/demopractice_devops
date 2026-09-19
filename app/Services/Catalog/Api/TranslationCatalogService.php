<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\Repositories\TranslationCatalogRepositoryContract;
use App\Contracts\Catalog\Api\TranslationCatalogContract;
use Illuminate\Support\Collection;

final class TranslationCatalogService implements TranslationCatalogContract
{
    public function __construct(
        private readonly TranslationCatalogRepositoryContract $translations,
        private readonly CatalogCacheContract $cache
    ) {}

    public function translations(string $locale): Collection
    {
        $cacheKey = 'catalog.translations.'.$this->cache->version().'.'.$locale;

        return $this->cache->remember(
            $cacheKey,
            false,
            fn (): Collection => $this->translations->activeForLocale($locale)
        );
    }
}
