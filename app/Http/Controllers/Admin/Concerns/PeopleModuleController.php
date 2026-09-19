<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Contracts\Admin\People\PersonSummaryContract;
use App\Contracts\Location\UserLocationContract;
use App\Data\Admin\People\PersonSummary;
use App\Models\User;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class PeopleModuleController extends AdminModuleController
{
    protected string $role;

    protected string $profileRelation;

    protected string $profileModel;

    protected array $profileFields = [];

    public function __construct(
        AdminModuleServices $modules,
        private readonly UserLocationContract $location,
        private readonly PersonSummaryContract $summaries,
    ) {
        parent::__construct($modules);
    }

    public function show(int|string $id): View
    {
        $view = parent::show($id);
        $record = $view->getData()['record'];

        return $view->with('summary', $record instanceof User ? $this->summaries->for($record) : PersonSummary::empty());
    }

    protected function rules(array $module, ?Model $record = null): array
    {
        $rules = parent::rules($module, $record) + $this->location->rules((bool) ($module['location_required'] ?? true));
        if ($record) {
            $relationName = explode('.', $this->profileRelation)[0];
            $profileId = $record->{$relationName}?->getKey();
            if ($profileId) {
                foreach ($this->profileFields as $field) {
                    if (! isset($rules[$field])) {
                        continue;
                    }
                    $rules[$field] = array_map(static function ($rule) use ($profileId) {
                        if (is_string($rule) && str_starts_with($rule, 'unique:')) {
                            $parts = explode(',', $rule);
                            if (count($parts) >= 3) {
                                $parts[2] = (string) $profileId;
                            }

                            return implode(',', $parts);
                        }

                        return $rule;
                    }, $rules[$field]);
                }
            }
        }

        return $rules;
    }

    /**
     * The raw location inputs are swapped for the resolved users-table columns
     * (codes plus names); an optional location left blank changes nothing.
     */
    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $location = $this->location->attributes($validated);
        $data = array_diff_key(parent::prepareData($validated, $request, $module), array_flip(UserLocationContract::FIELDS));

        return $data + $location;
    }

    protected function persist(array $data, ?Model $record): Model
    {
        return DB::transaction(function () use ($data, $record): Model {
            $profile = [];
            foreach ($this->profileFields as $field) {
                if (array_key_exists($field, $data)) {
                    $profile[$field] = $data[$field];
                    unset($data[$field]);
                }
            }
            $data['role'] = $this->role;
            // An admin-typed password counts as a real one (the app then asks for it on Change Password).
            $passwordTyped = filled($data['password'] ?? null);
            if (! $record) {
                $data['status'] ??= $this->role === User::ROLE_DEALER ? 'pending_approval' : 'active';
                $data['password'] ??= Str::password(20);
                $record = User::query()->create($data);
            } else {
                $record->fill($data)->save();
            }
            if ($passwordTyped) {
                $record->forceFill(['password_set_at' => now()])->save();
            }
            if ($profile !== []) {
                $profile['user_id'] = $record->id;
                $this->profileModel::query()->updateOrCreate(['user_id' => $record->id], $profile);
            }

            return $record->fresh($this->profileRelation);
        });
    }

    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);
        foreach ($this->profileFields as $field) {
            $data[$field] = data_get($record, $this->profileRelation.'.'.$field);
        }

        foreach (UserLocationContract::FIELDS as $field) {
            $data[$field] = $record->getAttribute($field);
        }

        // A taluka typed by hand has a name but no code.
        if (blank($data['subdistrict_code']) && filled($data['subdistrict_name'])) {
            $data['subdistrict_code'] = UserLocationContract::OTHER_SUBDISTRICT;
        }

        return $data;
    }
}
