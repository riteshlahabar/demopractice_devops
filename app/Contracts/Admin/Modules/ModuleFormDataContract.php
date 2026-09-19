<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * SRP: the values and select options a module form is rendered with.
 */
interface ModuleFormDataContract
{
    /**
     * Values pre-filled on a create form from the query string.
     *
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    public function forCreate(Request $request, array $module): array;

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>
     */
    public function forRecord(Model $record, array $module): array;

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, array<mixed>>
     */
    public function options(array $module): array;

    /**
     * Extra data-* attributes per select option, used by conditional fields.
     *
     * @param  array<string, mixed>  $module
     * @return array<string, array<mixed>>
     */
    public function optionAttributes(array $module): array;

    /**
     * Choices for the listing toolbar's configured filters, keyed by filter
     * name. Only filters that declare `options` or an `option_model` appear.
     *
     * @param  array<string, mixed>  $module
     * @return array<string, array<mixed>>
     */
    public function filterOptions(array $module): array;
}
