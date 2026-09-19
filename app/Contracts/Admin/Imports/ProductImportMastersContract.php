<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: creating the category, brand, product type and unit records a product
 * row refers to, so the import does not fail on a missing master.
 */
interface ProductImportMastersContract
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function ensure(array $row): void;
}
