<?php

namespace App\Services\Hr;

use App\Contracts\Hr\HrmsSettingsContract;
use App\Models\Hr\HrmsSetting;
use Illuminate\Support\Facades\Cache;
use Throwable;

class HrmsSettingsService implements HrmsSettingsContract
{
    private const CACHE_KEY = 'hrms.settings.current';

    private const CACHE_MINUTES = 60;

    public function current(): HrmsSetting
    {
        try {
            $attributes = Cache::remember(
                self::CACHE_KEY,
                now()->addMinutes(self::CACHE_MINUTES),
                static fn (): ?array => HrmsSetting::query()->orderBy('id')->first()?->getAttributes()
            );
        } catch (Throwable) {
            $attributes = null;
        }

        return $attributes === null ? new HrmsSetting : (new HrmsSetting)->forceFill($attributes);
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
