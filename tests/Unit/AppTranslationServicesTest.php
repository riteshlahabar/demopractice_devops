<?php

namespace Tests\Unit;

use App\Contracts\Catalog\TextTranslatorContract;
use App\Contracts\Localization\AppTranslationRepositoryContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Contracts\Localization\WebsiteTranslationLookupContract;
use App\Services\Localization\AppTranslationBatchService;
use App\Services\Localization\AppTranslationCatalogService;
use RuntimeException;
use Tests\TestCase;

class AppTranslationServicesTest extends TestCase
{
    public function test_register_skips_write_when_nothing_changed(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->english['dealer'] = ['cart.title' => 'Cart'];
        $service = new AppTranslationCatalogService($repository, new FakeLocales);

        $this->assertSame(1, $service->register('dealer', ['cart.title' => 'Cart']));
        $this->assertSame(0, $repository->saveEnglishCalls);
    }

    public function test_register_marks_keys_whose_english_changed(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->english['dealer'] = ['cart.title' => 'Cart'];
        $service = new AppTranslationCatalogService($repository, new FakeLocales);

        $service->register('dealer', ['cart.title' => 'My Cart', 'cart.empty' => 'Empty', 'bad' => '']);

        $this->assertSame(1, $repository->saveEnglishCalls);
        $this->assertSame(['cart.title'], $repository->lastChangedKeys);
        $this->assertSame(['cart.title' => 'My Cart', 'cart.empty' => 'Empty'], $repository->lastSavedItems);
    }

    public function test_batch_translates_missing_pairs_and_reuses_same_english(): void
    {
        config(['localization.app_translate_batch_size' => 10]);
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [
            ['app' => 'dealer', 'key' => 'common.cancel', 'english' => 'Cancel'],
            ['app' => 'customer', 'key' => 'common.cancel', 'english' => 'Cancel'],
        ];
        $translator = new FakeTranslator;

        $result = $this->batch($repository, $translator)->translateNextBatch(null);

        // 2 rows x 2 languages: first of each language hits Google, second is reused.
        $this->assertSame(['translated' => 2, 'website' => 0, 'reused' => 2, 'failed' => 0, 'remaining' => 0], $result);
        $this->assertSame(2, $translator->calls);
        $this->assertSame('hi:Cancel', $repository->saved['customer|common.cancel|hi']);
        $this->assertSame('app', $repository->sources['customer|common.cancel|hi']);
    }

    public function test_batch_copies_website_translation_by_english_text_ignoring_case_and_spaces(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [['app' => 'customer', 'key' => 'cart.add', 'english' => 'Add  to cart']];
        $website = new FakeWebsiteLookup([
            ['locale' => 'hi', 'english' => 'Add To Cart', 'value' => 'कार्ट में जोड़ें'],
            ['locale' => 'mr', 'english' => 'Add To Cart', 'value' => 'Add To Cart'],
        ]);
        $translator = new FakeTranslator;

        $result = $this->batch($repository, $translator, $website)->translateNextBatch('customer');

        $this->assertSame(1, $result['website']);
        $this->assertSame('कार्ट में जोड़ें', $repository->saved['customer|cart.add|hi']);
        $this->assertSame('website', $repository->sources['customer|cart.add|hi']);
        // The Marathi "translation" equals the English, so it is not trusted.
        $this->assertSame('google', $repository->sources['customer|cart.add|mr']);
        $this->assertSame(1, $translator->calls);
    }

    public function test_batch_never_copies_placeholder_text_from_website(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [['app' => 'dealer', 'key' => 'cart.left', 'english' => 'Only {n} left']];
        $website = new FakeWebsiteLookup([['locale' => 'hi', 'english' => 'Only {n} left', 'value' => 'website value']]);

        $this->batch($repository, new FakeTranslator, $website)->translateNextBatch('dealer');

        $this->assertSame('google', $repository->sources['dealer|cart.left|hi']);
    }

    public function test_batch_leaves_existing_translations_alone(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [['app' => 'dealer', 'key' => 'cart.title', 'english' => 'Cart']];
        $repository->index = [
            'dealer|cart.title|hi' => ['value' => 'hand fixed', 'english' => 'Cart'],
            'dealer|cart.title|mr' => ['value' => 'hand fixed mr', 'english' => 'Cart'],
        ];
        $translator = new FakeTranslator;

        $result = $this->batch($repository, $translator, new FakeWebsiteLookup([['locale' => 'hi', 'english' => 'Cart', 'value' => 'x']]))->translateNextBatch('dealer');

        $this->assertSame(0, $result['remaining']);
        $this->assertSame([], $repository->saved);
        $this->assertSame(0, $translator->calls);
    }

    public function test_batch_stops_after_repeated_translator_failures(): void
    {
        $repository = new FakeAppTranslationRepository;
        $repository->rows = [
            ['app' => 'dealer', 'key' => 'a.one', 'english' => 'One'],
            ['app' => 'dealer', 'key' => 'a.two', 'english' => 'Two'],
            ['app' => 'dealer', 'key' => 'a.three', 'english' => 'Three'],
        ];
        $translator = new FakeTranslator;
        $translator->fail = true;

        $result = $this->batch($repository, $translator)->translateNextBatch('dealer');

        $this->assertSame(3, $translator->calls);
        $this->assertSame(3, $result['failed']);
        $this->assertSame(6, $result['remaining']);
    }

    private function batch(FakeAppTranslationRepository $repository, FakeTranslator $translator, ?FakeWebsiteLookup $website = null): AppTranslationBatchService
    {
        return new AppTranslationBatchService($repository, $website ?? new FakeWebsiteLookup([]), new FakeLocales, $translator);
    }
}

class FakeWebsiteLookup implements WebsiteTranslationLookupContract
{
    public function __construct(private readonly array $rows) {}

    public function translationsFor(array $locales): array
    {
        return array_values(array_filter($this->rows, fn ($row) => in_array($row['locale'], $locales, true)));
    }
}

class FakeLocales implements SupportedLocalesContract
{
    public function codes(): array
    {
        return ['en', 'hi', 'mr'];
    }

    public function translatable(): array
    {
        return ['hi', 'mr'];
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->codes(), true);
    }

    public function default(): string
    {
        return 'en';
    }
}

class FakeTranslator implements TextTranslatorContract
{
    public int $calls = 0;

    public bool $fail = false;

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        $this->calls++;

        if ($this->fail) {
            throw new RuntimeException('translator down');
        }

        return $targetLocale.':'.$text;
    }
}

class FakeAppTranslationRepository implements AppTranslationRepositoryContract
{
    /** @var array<string, array<string, string>> */
    public array $english = [];

    /** @var array<int, array{app: string, key: string, english: string}> */
    public array $rows = [];

    /** @var array<string, string> */
    public array $saved = [];

    /** @var array<string, string> */
    public array $sources = [];

    /** @var array<string, array{value: string, english: string}> */
    public array $index = [];

    public int $saveEnglishCalls = 0;

    public array $lastChangedKeys = [];

    public array $lastSavedItems = [];

    public function englishFor(string $app, string $defaultLocale): array
    {
        return $this->english[$app] ?? [];
    }

    public function saveEnglish(string $app, string $defaultLocale, array $items, array $changedKeys): void
    {
        $this->saveEnglishCalls++;
        $this->lastSavedItems = $items;
        $this->lastChangedKeys = $changedKeys;
    }

    public function translationsFor(string $app, string $locale): array
    {
        return [];
    }

    public function englishRows(?string $app, string $defaultLocale): array
    {
        return array_values(array_filter($this->rows, fn ($row) => $app === null || $row['app'] === $app));
    }

    public function translatedIndex(?string $app, array $locales): array
    {
        return $this->index;
    }

    public function saveTranslations(array $rows): void
    {
        foreach ($rows as $row) {
            $this->saved[$row['app'].'|'.$row['key'].'|'.$row['locale']] = $row['value'];
            $this->sources[$row['app'].'|'.$row['key'].'|'.$row['locale']] = $row['source'];
        }
    }
}
