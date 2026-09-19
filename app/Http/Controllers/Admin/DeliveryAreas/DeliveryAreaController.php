<?php

namespace App\Http\Controllers\Admin\DeliveryAreas;

use App\Contracts\Location\DistrictSelectionContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryAreaController extends AdminModuleController
{
    protected string $moduleKey = 'delivery-areas';

    public function __construct(AdminModuleServices $modules, private readonly DistrictSelectionContract $district)
    {
        parent::__construct($modules);
    }

    protected function rules(array $module, ?Model $record = null): array
    {
        $rules = parent::rules($module, $record) + $this->district->rules();
        $rules['district_code'][] = Rule::unique('delivery_areas', 'district_code')->ignore($record?->getKey());

        return $rules;
    }

    /**
     * The raw State / District inputs are swapped for the stored codes plus names.
     */
    protected function prepareData(array $validated, Request $request, array $module): array
    {
        return array_diff_key(parent::prepareData($validated, $request, $module), array_flip(DistrictSelectionContract::FIELDS))
            + $this->district->attributes($validated);
    }

    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);

        foreach (DistrictSelectionContract::FIELDS as $field) {
            $data[$field] = $record->getAttribute($field);
        }

        return $data;
    }
}
