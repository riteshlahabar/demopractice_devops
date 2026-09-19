<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\ProductTranslationContract;
use App\Contracts\Catalog\Product\TextTranslatorContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductTranslation;

final class ProductTranslationService implements ProductTranslationContract
{
    public function __construct(
        private readonly TextTranslatorContract $translator,
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

    public function translatePayload(string $name, ?string $description): array
    {
        $translations = [];
        foreach ($this->locales() as $locale) {
            $translations[$locale] = [
                'name' => $this->translator->translate($name, 'en', $locale),
                'description' => filled($description) ? $this->translator->translate((string) $description, 'en', $locale) : '',
            ];
        }

        return $translations;
    }

    public function extract(array &$data): array
    {
        $translations = [];
        foreach ($this->locales() as $locale) {
            $nameKey = 'translation_'.$locale.'_name';
            $descriptionKey = 'translation_'.$locale.'_description';
            $translations[$locale] = [
                'name' => trim((string) ($data[$nameKey] ?? '')),
                'description' => trim((string) ($data[$descriptionKey] ?? '')),
            ];
            unset($data[$nameKey], $data[$descriptionKey]);
        }

        return $translations;
    }

    public function sync(Product $product, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $name = trim((string) ($translation['name'] ?? ''));
            $description = trim((string) ($translation['description'] ?? ''));

            if ($name === '' && $description === '') {
                ProductTranslation::query()->where('product_id', $product->getKey())->where('locale', $locale)->delete();

                continue;
            }

            ProductTranslation::query()->updateOrCreate(
                ['product_id' => $product->getKey(), 'locale' => $locale],
                ['name' => $name !== '' ? $name : $product->name, 'description' => $description !== '' ? $description : null]
            );
        }
    }

    public function formData(Product $product): array
    {
        $translations = $product->relationLoaded('translations') ? $product->translations : $product->translations()->get();
        $data = [];
        foreach ($this->locales() as $locale) {
            $translation = $translations->firstWhere('locale', $locale);
            $data['translation_'.$locale.'_name'] = $translation?->name;
            $data['translation_'.$locale.'_description'] = $translation?->description;
        }

        return $data;
    }
}
