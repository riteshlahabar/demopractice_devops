<?php

namespace Tests\Unit;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use App\Contracts\Catalog\Api\Repositories\TranslationCatalogRepositoryContract;
use App\Services\Catalog\Api\TranslationCatalogService;
use Closure;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class TranslationCatalogServiceTest extends TestCase
{
    public function test_service_uses_locale_specific_cache_and_repository(): void
    {
        $repository = new class implements TranslationCatalogRepositoryContract
        {
            public function activeForLocale(string $locale): Collection
            {
                return collect(['welcome' => $locale.' value']);
            }
        };

        $cache = new class implements CatalogCacheContract
        {
            public string $key = '';

            public bool $fresh = true;

            public function version(): int
            {
                return 3;
            }

            public function remember(string $key, bool $fresh, Closure $loader): mixed
            {
                $this->key = $key;
                $this->fresh = $fresh;

                return $loader();
            }
        };

        $result = (new TranslationCatalogService($repository, $cache))->translations('mr');

        $this->assertSame(['welcome' => 'mr value'], $result->all());
        $this->assertSame('catalog.translations.3.mr', $cache->key);
        $this->assertFalse($cache->fresh);
    }
}
