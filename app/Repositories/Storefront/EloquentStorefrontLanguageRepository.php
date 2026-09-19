<?php

namespace App\Repositories\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontLanguageRepositoryContract;
use App\Models\Communication\Language;
use Illuminate\Support\Collection;

final class EloquentStorefrontLanguageRepository implements StorefrontLanguageRepositoryContract
{
    public function activeLanguages(): Collection
    {
        return Language::query()->active()->ordered()->get();
    }

    public function activeByCode(string $locale): Language
    {
        return Language::query()
            ->active()
            ->where('code', $locale)
            ->firstOrFail();
    }
}
