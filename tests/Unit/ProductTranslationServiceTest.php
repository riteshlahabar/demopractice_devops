<?php

namespace Tests\Unit;

use App\Contracts\Catalog\ProductTranslationRepositoryContract;
use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Services\Catalog\ProductTranslationService;
use PHPUnit\Framework\TestCase;

class ProductTranslationServiceTest extends TestCase
{
    /** Stands in for the admin Languages list, which needs no database here. */
    private function locales(): SupportedLocalesContract
    {
        return new class implements SupportedLocalesContract
        {
            public function codes(): array
            {
                return ['en', 'hi', 'mr', 'gu', 'pa', 'te'];
            }

            public function translatable(): array
            {
                return ['hi', 'mr', 'gu', 'pa', 'te'];
            }

            public function isSupported(string $locale): bool
            {
                return in_array($locale, $this->codes(), true);
            }

            public function default(): string
            {
                return 'en';
            }
        };
    }

    public function test_translation_service_uses_replaceable_contracts(): void
    {
        $translator = new class implements TextTranslatorContract
        {
            public function translate(
                string $text,
                string $sourceLocale,
                string $targetLocale
            ): string {
                return $targetLocale.':'.$text;
            }
        };

        $repository = new class implements ProductTranslationRepositoryContract
        {
            public array $rows = [];

            public function getByProductId(int $productId): array
            {
                return $this->rows;
            }

            public function deleteLocale(
                int $productId,
                string $locale
            ): void {
                unset($this->rows[$locale]);
            }

            public function upsert(
                int $productId,
                string $locale,
                string $name,
                ?string $description
            ): void {
                $this->rows[$locale] = [
                    'name' => $name,
                    'description' => $description,
                ];
            }
        };

        $service = new ProductTranslationService(
            $translator,
            $repository,
            $this->locales()
        );

        $result = $service->translatePayload(
            'Product',
            'Description'
        );

        $this->assertSame(
            'mr:Product',
            $result['mr']['name']
        );

        $this->assertSame(
            'hi:Description',
            $result['hi']['description']
        );
    }

    public function test_translation_input_is_extracted_from_product_data(): void
    {
        $translator = new class implements TextTranslatorContract
        {
            public function translate(
                string $text,
                string $sourceLocale,
                string $targetLocale
            ): string {
                return $text;
            }
        };

        $repository = new class implements ProductTranslationRepositoryContract
        {
            public function getByProductId(int $productId): array
            {
                return [];
            }

            public function deleteLocale(
                int $productId,
                string $locale
            ): void {}

            public function upsert(
                int $productId,
                string $locale,
                string $name,
                ?string $description
            ): void {}
        };

        $service = new ProductTranslationService(
            $translator,
            $repository,
            $this->locales()
        );

        $data = [
            'name' => 'Test Product',
            'translation_mr_name' => 'चाचणी उत्पादन',
            'translation_mr_description' => 'वर्णन',
        ];

        $translations = $service->extract($data);

        $this->assertSame(
            'चाचणी उत्पादन',
            $translations['mr']['name']
        );

        $this->assertArrayNotHasKey(
            'translation_mr_name',
            $data
        );

        $this->assertSame(
            'Test Product',
            $data['name']
        );
    }
}
