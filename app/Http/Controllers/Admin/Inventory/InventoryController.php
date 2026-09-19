<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InventoryController extends AdminModuleController
{
    protected string $moduleKey = 'inventory';

    protected function rules(array $module, ?Model $record = null): array
    {
        $rules = parent::rules($module, $record);
        $rules['product_variant_id'] = [
            'nullable',
            Rule::exists('product_variants', 'id')->where(fn ($query) => $query->where('product_id', request()->integer('product_id'))),
        ];

        return $rules;
    }
}
