<?php

namespace App\Repositories\Location;

use App\Contracts\Location\LocationDirectoryContract;
use App\Models\Location\LgdDistrict;
use App\Models\Location\LgdLocalBody;
use App\Models\Location\LgdState;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * The directory changes only when the CSVs are re-imported, so every list is
 * cached for a day; `php artisan lgd:import` clears it.
 */
final class EloquentLocationDirectory implements LocationDirectoryContract
{
    public const CACHE_PREFIX = 'lgd:';

    public const VERSION_KEY = 'lgd:version';

    private const TTL_SECONDS = 86400;

    public function __construct(private readonly Cache $cache) {}

    public function states(): array
    {
        return $this->remember($this->key('states'), fn (): array => $this->sorted(
            LgdState::query()->pluck('name', 'state_code')->all()
        ));
    }

    public function districts(int $stateCode): array
    {
        return $this->remember($this->key('districts:'.$stateCode), fn (): array => $this->sorted(
            LgdDistrict::query()->where('state_code', $stateCode)->pluck('name', 'district_code')->all()
        ));
    }

    public function subdistricts(int $districtCode): array
    {
        return $this->remember($this->key('subdistricts:'.$districtCode), fn (): array => $this->sorted(
            LgdLocalBody::query()
                ->where('district_code', $districtCode)
                ->whereNotNull('subdistrict_code')->where('subdistrict_code', '!=', '')
                ->distinct()
                ->pluck('subdistrict_name', 'subdistrict_code')
                ->all()
        ));
    }

    /**
     * Like Cache::remember, but an empty list is not cached, so data imported
     * straight into the tables (phpMyAdmin) appears without clearing the cache.
     *
     * @param  callable(): array<int|string, string>  $load
     * @return array<int|string, string>
     */
    private function remember(string $key, callable $load): array
    {
        $cached = $this->cache->get($key);

        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $items = $load();

        if ($items !== []) {
            $this->cache->put($key, $items, self::TTL_SECONDS);
        }

        return $items;
    }

    /**
     * Keys carry a version so a re-import can retire every cached list at once.
     */
    private function key(string $suffix): string
    {
        return self::CACHE_PREFIX.'v'.$this->cache->get(self::VERSION_KEY, 1).':'.$suffix;
    }

    /**
     * The files mix "MAHARASHTRA" and "Alluri Sitharama Raju"; all-caps names
     * are shown in title case so dropdowns read consistently.
     *
     * @param  array<int|string, string|null>  $items
     * @return array<int|string, string>
     */
    private function sorted(array $items): array
    {
        $items = array_map(function (?string $name): string {
            $name = trim((string) $name);

            return $name === mb_strtoupper($name) ? mb_convert_case(mb_strtolower($name), MB_CASE_TITLE) : $name;
        }, $items);

        asort($items, SORT_NATURAL | SORT_FLAG_CASE);

        return $items;
    }
}
