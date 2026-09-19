<?php

namespace App\Http\Controllers\Api\Localization;

use App\Contracts\Localization\AppLanguageSettingsContract;
use App\Contracts\Localization\AppTranslationCatalogContract;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Localization\AppTranslationIndexRequest;
use App\Http\Requests\Api\Localization\AppTranslationRegisterRequest;
use Illuminate\Http\JsonResponse;

/**
 * Mobile-app UI strings. The apps never trigger translation themselves: they
 * register their English text and read whatever the admin has translated.
 */
final class AppTranslationController extends ApiController
{
    public function __construct(
        private readonly AppTranslationCatalogContract $catalog,
        private readonly AppLanguageSettingsContract $appLanguages,
    ) {}

    public function index(AppTranslationIndexRequest $request): JsonResponse
    {
        $app = (string) $request->validated('app');
        $locale = (string) $request->validated('locale');

        return $this->success([
            'app' => $app,
            'locale' => $locale,
            // Only the languages the admin switched on for this app (Translation → App Languages).
            'available_locales' => $this->appLanguages->activeFor($app),
            'translations' => (object) $this->catalog->translations($app, $locale),
        ]);
    }

    public function register(AppTranslationRegisterRequest $request): JsonResponse
    {
        $app = (string) $request->validated('app');

        return $this->success([
            'app' => $app,
            'registered' => $this->catalog->register($app, $request->items()),
        ]);
    }
}
