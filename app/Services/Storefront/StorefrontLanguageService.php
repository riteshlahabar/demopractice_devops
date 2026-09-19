<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Repositories\StorefrontLanguageRepositoryContract;
use App\Contracts\Storefront\StorefrontLanguageContract;
use App\Models\Communication\Language;
use Illuminate\Http\Request;

final class StorefrontLanguageService implements StorefrontLanguageContract
{
    public function __construct(
        private readonly StorefrontLanguageRepositoryContract $languages
    ) {}

    public function data(Request $request): array
    {
        $languages = $this->languages->activeLanguages();
        if ($languages->isEmpty()) {
            return $this->emptyData();
        }

        $requestedLocale = (string) $request->session()->get('store_locale', '');
        $currentLanguage = $languages->firstWhere('code', $requestedLocale)
            ?: $languages->firstWhere('is_default', true)
            ?: $languages->firstWhere('code', 'en')
            ?: $languages->first();

        app()->setLocale($currentLanguage->code);

        return [$languages, $currentLanguage];
    }

    public function emptyData(): array
    {
        $language = new Language([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [collect([$language]), $language];
    }

    public function switchLocale(Request $request, string $locale): void
    {
        $language = $this->languages->activeByCode($locale);
        $request->session()->put('store_locale', $language->code);
    }
}
