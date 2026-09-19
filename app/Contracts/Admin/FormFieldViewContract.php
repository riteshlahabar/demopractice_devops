<?php

namespace App\Contracts\Admin;

/**
 * SRP: maps a custom field type to the partial that draws it.
 *
 * Separate from the tree so adding a widget and changing the layout rules are
 * independent changes.
 */
interface FormFieldViewContract
{
    /**
     * @return array{view: string, wrap: bool}|null Null when the type has no
     *                                              custom partial and the
     *                                              standard control is used.
     */
    public function resolve(string $type): ?array;
}
