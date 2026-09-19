<?php

namespace App\Contracts\Storefront\Repositories;

use App\Models\Communication\Language;
use Illuminate\Support\Collection;

interface StorefrontLanguageRepositoryContract
{
    public function activeLanguages(): Collection;

    public function activeByCode(string $locale): Language;
}
