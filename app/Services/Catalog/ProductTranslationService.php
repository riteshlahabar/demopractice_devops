<?php

namespace App\Services\Catalog;

use App\Contracts\Catalog\ProductTranslationRepositoryContract;
use App\Contracts\Catalog\ProductTranslationServiceContract;
use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Models\Catalog\Product;

/**
 * SRP:
 * Coordinates Product translation business rules only.
 *
 * OCP:
 * Translation provider and persistence implementation are replaceable.
 *
 * DIP:
 * Depends only on focused contracts.
 */
class ProductTranslationService implements ProductTranslationServiceContract
{
    public function __construct(
        private readonly TextTranslatorContract $translator,
        private readonly ProductTranslationRepositoryContract $repository,
        private readonly SupportedLocalesContract $supportedLocales
    ) {}

    /**
     * The admin Languages list is the single source of truth, so adding or
     * disabling a language in admin is enough; this no longer keeps a copy.
     *
     * @return array<int, string>
     */
    private function locales(): array
    {
        return $this->supportedLocales->translatable();
    }

    public function translatePayload(
        string $name,
        ?string $description
    ): array {
        $translations = [];

        foreach ($this->locales() as $locale) {
            $translations[$locale] = [
                'name' => $this->translator->translate(
                    $name,
                    'en',
                    $locale
                ),

                'description' => filled($description)
                    ? $this->translator->translate(
                        (string) $description,
                        'en',
                        $locale
                    )
                    : '',
            ];
        }

        return $translations;
    }

    public function extract(array &$data): array
    {
        $translations = [];

        foreach ($this->locales() as $locale) {
            $nameKey =
                'translation_'.$locale.'_name';

            $descriptionKey =
                'translation_'.$locale.'_description';

            $translations[$locale] = [
                'name' => trim(
                    (string) ($data[$nameKey] ?? '')
                ),

                'description' => trim(
                    (string) ($data[$descriptionKey] ?? '')
                ),
            ];

            unset(
                $data[$nameKey],
                $data[$descriptionKey]
            );
        }

        return $translations;
    }

    public function sync(
        Product $product,
        array $translations
    ): void {
        foreach ($translations as $locale => $translation) {
            $name =
                trim(
                    (string) ($translation['name'] ?? '')
                );

            $description =
                trim(
                    (string) ($translation['description'] ?? '')
                );

            if ($name === '' && $description === '') {
                $this->repository->deleteLocale(
                    (int) $product->getKey(),
                    $locale
                );

                continue;
            }

            $this->repository->upsert(
                (int) $product->getKey(),
                $locale,
                $name !== ''
                    ? $name
                    : (string) $product->name,
                $description !== ''
                    ? $description
                    : null
            );
        }
    }

    public function formData(Product $product): array
    {
        $translations =
            $this->repository->getByProductId(
                (int) $product->getKey()
            );

        $data = [];

        foreach ($this->locales() as $locale) {
            $translation =
                $translations[$locale] ?? [];

            $data[
                'translation_'.$locale.'_name'
            ] = $translation['name'] ?? null;

            $data[
                'translation_'.$locale.'_description'
            ] = $translation['description'] ?? null;
        }

        return $data;
    }
}
