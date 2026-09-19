<?php

namespace App\Contracts\Catalog\Api;

/**
 * Translates admin-entered storefront text (section titles, banner and offer
 * text, units) for the app API with the same keys the website uses, so a
 * phrase translated once serves both.
 */
interface CatalogTextTranslatorContract
{
    /**
     * @param  string  $group  website translation group, e.g. homepage_section, homepage_entry, homepage_button, unit
     */
    public function text(?string $text, string $group): ?string;
}
