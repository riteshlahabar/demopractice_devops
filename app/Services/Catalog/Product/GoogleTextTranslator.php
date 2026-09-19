<?php

namespace App\Services\Catalog\Product;

use App\Contracts\Catalog\Product\TextTranslatorContract;
use Illuminate\Support\Facades\Http;

final class GoogleTextTranslator implements TextTranslatorContract
{
    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        $response = Http::timeout(12)->retry(1, 250)->get(
            'https://translate.googleapis.com/translate_a/single',
            ['client' => 'gtx', 'sl' => $sourceLocale, 'tl' => $targetLocale, 'dt' => 't', 'q' => $text]
        );

        if (! $response->successful()) {
            abort(422, 'Auto translation failed. Please enter translations manually.');
        }

        return collect($response->json()[0] ?? [])
            ->map(fn ($segment): string => (string) ($segment[0] ?? ''))
            ->implode('');
    }
}
