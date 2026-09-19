<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogCacheContract;
use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * SRP:
 * Laravel-specific Catalog cache implementation only.
 */
final class LaravelCatalogCache implements CatalogCacheContract
{
    public function version(): int
    {
        return (int) Cache::get(
            'catalog_cache_version',
            1
        );
    }

    public function remember(
        string $key,
        bool $fresh,
        Closure $loader
    ): mixed {
        if ($fresh) {
            return $loader();
        }

        return Cache::remember(
            $key,
            now()->addMinutes(
                max(
                    1,
                    (int) config(
                        'catalog.cache_minutes',
                        10
                    )
                )
            ),
            $loader
        );
    }
}
