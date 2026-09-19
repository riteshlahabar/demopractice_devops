<?php

namespace App\Services\Catalog;

use App\Contracts\Catalog\TextTranslatorContract;
use Illuminate\Support\Facades\Http;

/**
 * SRP:
 * Only knows how to translate text using Google Translate.
 *
 * LSP:
 * Can be replaced by another TextTranslatorContract implementation.
 */
class GoogleTextTranslator implements TextTranslatorContract
{
    public function translate(
        string $text,
        string $sourceLocale,
        string $targetLocale
    ): string {
        $response = Http::timeout(12)
            ->retry(1, 250)
            ->get(
                'https://translate.googleapis.com/translate_a/single',
                [
                    'client' => 'gtx',
                    'sl' => $sourceLocale,
                    'tl' => $targetLocale,
                    'dt' => 't',
                    'q' => $text,
                ]
            );

        if (! $response->successful()) {
            abort(
                422,
                'Auto translation failed. Please enter translations manually.'
            );
        }

        $segments = $response->json()[0] ?? [];

        return collect($segments)
            ->map(
                fn ($segment): string => (string) ($segment[0] ?? '')
            )
            ->implode('');
    }
}
