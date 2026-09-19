<?php

namespace App\Services\Catalog\Api;

use App\Contracts\Catalog\Api\CatalogTextTranslatorContract;

/**
 * Uses the website's storefront_public_t() pipeline: web_translations first,
 * then an automatic translation saved for next time.
 */
final class StorefrontCatalogTextTranslator implements CatalogTextTranslatorContract
{
    public function text(?string $text, string $group): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        return storefront_public_t($text, $group);
    }
}
