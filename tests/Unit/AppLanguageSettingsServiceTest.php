<?php

namespace Tests\Unit;

use App\Contracts\Localization\SupportedLocalesContract;
use App\Services\Localization\AppLanguageSettingsService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AppLanguageSettingsServiceTest extends TestCase
{
    public function test_languages_are_on_by_default_and_english_can_never_be_switched_off(): void
    {
        $service = new AppLanguageSettingsService($this->locales());

        Cache::put('app_languages_disabled', ['dealer' => ['gu', 'en']]);

        $this->assertSame(['en', 'hi', 'mr', 'gu'], $service->activeFor('customer'));
        $this->assertSame(['en', 'hi', 'mr'], $service->activeFor('dealer'));

        $matrix = collect($service->matrix())->keyBy('code');
        $this->assertTrue($matrix['en']['apps']['dealer']);
        $this->assertFalse($matrix['gu']['apps']['dealer']);
        $this->assertTrue($matrix['gu']['apps']['salesman']);
    }

    private function locales(): SupportedLocalesContract
    {
        return new class implements SupportedLocalesContract
        {
            public function codes(): array
            {
                return ['en', 'hi', 'mr', 'gu'];
            }

            public function translatable(): array
            {
                return ['hi', 'mr', 'gu'];
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
}
