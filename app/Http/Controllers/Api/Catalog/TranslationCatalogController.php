<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Contracts\Catalog\Api\TranslationCatalogContract;
use App\Contracts\Localization\AppStringTranslationContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Catalog\TranslationCatalogRequest;
use App\Http\Requests\Api\Catalog\TranslationSyncRequest;
use Illuminate\Http\JsonResponse;

final class TranslationCatalogController extends ApiController
{
    public function __construct(
        private readonly TranslationCatalogContract $catalog,
        private readonly AppStringTranslationContract $appStrings,
        private readonly SupportedLocalesContract $locales
    ) {}

    /**
     * Every stored translation for a locale, plus the languages on offer so the
     * app can build its language picker from one call.
     */
    public function index(TranslationCatalogRequest $request): JsonResponse
    {
        $locale = (string) $request->validated('locale');

        return $this->success([
            'locale' => $locale,
            'available_locales' => $this->locales->codes(),
            'translations' => $this->catalog->translations($locale),
        ]);
    }

    /**
     * The app posts its English labels once per locale; anything missing is
     * translated and stored, then the whole map comes back.
     */
    public function sync(TranslationSyncRequest $request): JsonResponse
    {
        $locale = (string) $request->validated('locale');

        return $this->success([
            'locale' => $locale,
            'translations' => $this->appStrings->translate($locale, $request->items()),
        ]);
    }
}
