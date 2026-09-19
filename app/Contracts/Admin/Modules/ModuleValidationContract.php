<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Database\Eloquent\Model;

/**
 * SRP: turning a module's field config into validation rules, messages and
 * attribute names.
 */
interface ModuleValidationContract
{
    /**
     * @param  array<string, mixed>  $module
     * @return array<string, array<int, mixed>>
     */
    public function rules(array $module, ?Model $record = null): array;

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, string>
     */
    public function messages(array $module): array;

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, string>
     */
    public function attributes(array $module): array;
}
