<?php

namespace App\Http\Middleware;

use App\Contracts\Localization\SupportedLocalesContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives every API request a locale.
 *
 * The catalog presenters already call translatedName(); they returned English
 * only because nothing ever set the locale on an API request. Order of
 * preference: explicit ?locale=, then Accept-Language, then the signed-in
 * account's saved preference, then the app default.
 */
class SetApiLocale
{
    public function __construct(private readonly SupportedLocalesContract $locales) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->requested($request)
            ?? $this->preferred($request)
            ?? $this->locales->default();

        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function requested(Request $request): ?string
    {
        foreach ([$request->query('locale'), $request->header('Accept-Language')] as $candidate) {
            $code = $this->normalise((string) $candidate);

            if ($code !== null) {
                return $code;
            }
        }

        return null;
    }

    private function preferred(Request $request): ?string
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        foreach ([$user->customerProfile?->preferred_language, $user->language ?? null] as $candidate) {
            if (is_string($candidate) && $this->locales->isSupported($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Accept-Language can arrive as "mr-IN,mr;q=0.9,en;q=0.8" — take the first
     * entry that is actually enabled in admin.
     */
    private function normalise(string $header): ?string
    {
        if (trim($header) === '') {
            return null;
        }

        foreach (explode(',', $header) as $part) {
            $code = strtolower(trim(explode(';', $part)[0]));

            if ($code === '') {
                continue;
            }

            if ($this->locales->isSupported($code)) {
                return $code;
            }

            $base = explode('-', $code)[0];

            if ($this->locales->isSupported($base)) {
                return $base;
            }
        }

        return null;
    }
}
