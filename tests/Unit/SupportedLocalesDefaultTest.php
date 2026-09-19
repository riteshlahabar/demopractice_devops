<?php

namespace Tests\Unit;

use App\Services\Localization\DatabaseSupportedLocalesService;
use Tests\TestCase;

class SupportedLocalesDefaultTest extends TestCase
{
    public function test_default_locale_is_not_changed_by_the_request_locale(): void
    {
        config(['app.fallback_locale' => 'en']);
        app()->setLocale('gu');

        $this->assertSame('en', (new DatabaseSupportedLocalesService)->default());
    }
}
