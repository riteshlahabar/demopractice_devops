<?php

use App\Models\Communication\WebTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if (! function_exists('storefront_public_auto_translate')) {
    function storefront_public_auto_translate(?string $text, ?string $locale = null): string
    {
        $text = trim((string) $text);
        $locale = $locale ?: app()->getLocale();

        if ($text === '' || $locale === '' || $locale === 'en') {
            return $text;
        }

        $cacheKey = 'storefront_public_translate.'.$locale.'.'.sha1($text);
        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        try {
            $response = Http::timeout(5)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'en',
                'tl' => $locale,
                'dt' => 't',
                'q' => $text,
            ]);

            if (! $response->successful()) {
                return $text;
            }

            $payload = $response->json();
            $translated = collect($payload[0] ?? [])
                ->map(fn ($segment) => is_array($segment) ? (string) ($segment[0] ?? '') : '')
                ->implode('');

            $translated = trim($translated);

            if ($translated !== '') {
                Cache::put($cacheKey, $translated, now()->addDays(30));

                return $translated;
            }
        } catch (Throwable) {
            return $text;
        }

        return $text;
    }
}

if (! function_exists('storefront_public_t')) {
    function storefront_public_t(?string $text, string $group = 'public_text', ?string $locale = null): string
    {
        $fallback = trim((string) $text);

        if ($fallback === '') {
            return '';
        }

        $locale = $locale ?: app()->getLocale();
        $key = $group.'.'.sha1($fallback);

        return web_t($key, $fallback, [], $locale);
    }
}
if (! function_exists('web_t')) {
    function web_t(string $key, ?string $englishText = null, array $replace = [], ?string $locale = null): string
    {
        $fallback = $englishText ?? Str::of($key)->afterLast('.')->replace('_', ' ')->headline()->toString();
        $locale = $locale ?: app()->getLocale();

        if ($locale === '' || $locale === 'en') {
            return strtr($fallback, $replace);
        }

        $cacheVersion = (int) Cache::get('catalog_cache_version', 1);
        $cacheKey = 'web_translations.'.$cacheVersion.'.'.$locale;
        $translations = Cache::remember(
            $cacheKey,
            now()->addHours(6),
            fn () => WebTranslation::query()
                ->where('locale', $locale)
                ->where('is_active', true)
                ->pluck('value', 'translation_key')
                ->all()
        );

        $databaseValue = (string) ($translations[$key] ?? '');
        if ($databaseValue !== '') {
            return strtr($databaseValue, $replace);
        }

        $value = storefront_public_auto_translate($fallback, $locale);

        if ($value !== '' && $value !== $fallback) {
            WebTranslation::query()->updateOrCreate(
                ['translation_key' => $key, 'locale' => $locale],
                [
                    'group' => explode('.', $key, 2)[0],
                    'english_text' => $fallback,
                    'value' => $value,
                    'is_active' => true,
                ]
            );

            Cache::forget($cacheKey);
        }

        return strtr($value !== '' ? $value : $fallback, $replace);
    }
}
