<?php

namespace App\Contracts\Catalog\Api;

use Closure;

/**
 * ISP:
 * Focused only on Catalog cache operations.
 *
 * DIP:
 * Catalog services do not directly depend on Laravel Cache facade.
 */
interface CatalogCacheContract
{
    public function version(): int;

    public function remember(
        string $key,
        bool $fresh,
        Closure $loader
    ): mixed;
}
