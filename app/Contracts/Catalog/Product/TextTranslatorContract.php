<?php

namespace App\Contracts\Catalog\Product;

interface TextTranslatorContract
{
    public function translate(string $text, string $sourceLocale, string $targetLocale): string;
}
