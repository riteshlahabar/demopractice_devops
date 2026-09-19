<?php

namespace App\Contracts\Admin\Imports;

/**
 * SRP: turning the human readable names in a row (category, brand, warehouse)
 * into the foreign keys the model expects.
 */
interface ImportRelationResolverContract
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function resolve(array $data, array $row, string $module): array;
}
