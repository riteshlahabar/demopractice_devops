<?php

namespace App\Services\Localization;

use App\Contracts\Localization\AppTranslationCatalogContract;
use App\Contracts\Localization\AppTranslationRepositoryContract;
use App\Contracts\Localization\SupportedLocalesContract;

final class AppTranslationCatalogService implements AppTranslationCatalogContract
{
    public function __construct(
        private readonly AppTranslationRepositoryContract $repository,
        private readonly SupportedLocalesContract $locales,
    ) {}

    public function register(string $app, array $items): int
    {
        $default = $this->locales->default();
        $stored = $this->repository->englishFor($app, $default);
        $clean = [];
        $changed = [];

        foreach ($items as $key => $english) {
            $key = trim((string) $key);
            $english = trim((string) $english);

            if ($key === '' || $english === '') {
                continue;
            }

            $clean[$key] = $english;

            if (array_key_exists($key, $stored) && $stored[$key] !== $english) {
                $changed[] = $key;
            }
        }

        // Nothing new: skip the write entirely.
        if (array_diff_assoc($clean, $stored) === []) {
            return count($clean);
        }

        $this->repository->saveEnglish($app, $default, $clean, $changed);

        return count($clean);
    }

    public function translations(string $app, string $locale): array
    {
        return $this->repository->translationsFor($app, $locale);
    }
}
