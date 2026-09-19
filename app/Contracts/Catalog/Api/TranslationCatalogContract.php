<?php

namespace App\Contracts\Catalog\Api;

use Illuminate\Support\Collection;

interface TranslationCatalogContract
{
    public function translations(string $locale): Collection;
}
