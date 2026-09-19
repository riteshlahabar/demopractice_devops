<?php

namespace App\Services\Localization;

use App\Contracts\Localization\AppLanguageSettingsContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Models\Communication\AppLanguage;
use App\Models\Communication\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class AppLanguageSettingsService implements AppLanguageSettingsContract
{
    private const CACHE_KEY = 'app_languages_disabled';

    private const CACHE_MINUTES = 60;

    public function __construct(private readonly SupportedLocalesContract $locales) {}

    public function matrix(): array
    {
        $default = $this->locales->default();
        $disabled = $this->disabled();
        $languages = $this->languages();

        return array_map(fn (array $language): array => $language + [
            'is_default' => $language['code'] === $default,
            'apps' => array_combine($this->apps(), array_map(
                fn (string $app): bool => $language['code'] === $default || ! in_array($language['code'], $disabled[$app] ?? [], true),
                $this->apps()
            )),
        ], $languages);
    }

    public function save(array $enabled): void
    {
        $default = $this->locales->default();
        $now = now();
        $rows = [];

        foreach ($this->apps() as $app) {
            $on = array_map('strval', (array) ($enabled[$app] ?? []));

            foreach ($this->locales->codes() as $code) {
                $rows[] = [
                    'app' => $app,
                    'locale' => $code,
                    'is_active' => $code === $default || in_array($code, $on, true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(fn () => AppLanguage::query()->upsert($rows, ['app', 'locale'], ['is_active', 'updated_at']));
        Cache::forget(self::CACHE_KEY);
    }

    public function activeFor(string $app): array
    {
        $default = $this->locales->default();
        $disabled = $this->disabled()[$app] ?? [];

        return array_values(array_filter(
            $this->locales->codes(),
            static fn (string $code): bool => $code === $default || ! in_array($code, $disabled, true)
        ));
    }

    /**
     * @return array<int, string>
     */
    private function apps(): array
    {
        return array_keys((array) config('localization.apps', []));
    }

    /**
     * @return array<string, array<int, string>> app => switched-off codes
     */
    private function disabled(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_MINUTES), fn (): array => AppLanguage::query()
                ->where('is_active', false)
                ->get(['app', 'locale'])
                ->groupBy('app')
                ->map(fn ($rows): array => $rows->pluck('locale')->all())
                ->all());
        } catch (Throwable) {
            // Table not migrated yet or database down: everything stays on.
            return [];
        }
    }

    /**
     * @return array<int, array{code: string, name: string, native_name: string}>
     */
    private function languages(): array
    {
        try {
            $names = Language::query()->get(['code', 'name', 'native_name'])->keyBy('code');
        } catch (Throwable) {
            $names = collect();
        }

        return array_map(static fn (string $code): array => [
            'code' => $code,
            'name' => (string) ($names[$code]->name ?? strtoupper($code)),
            'native_name' => (string) ($names[$code]->native_name ?? ''),
        ], $this->locales->codes());
    }
}
