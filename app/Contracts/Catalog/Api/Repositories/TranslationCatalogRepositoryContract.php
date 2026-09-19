<?php

namespace App\Contracts\Catalog\Api\Repositories;

use Illuminate\Support\Collection;

interface TranslationCatalogRepositoryContract
{
    public function activeForLocale(string $locale): Collection;
}
