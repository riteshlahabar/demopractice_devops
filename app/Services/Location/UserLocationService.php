<?php

namespace App\Services\Location;

use App\Contracts\Location\LocationDirectoryContract;
use App\Contracts\Location\UserLocationContract;
use Illuminate\Validation\ValidationException;

final class UserLocationService implements UserLocationContract
{
    public function __construct(private readonly LocationDirectoryContract $directory) {}

    public function rules(bool $required = true): array
    {
        $presence = $required ? 'required' : 'nullable';
        $dependent = $required ? 'required' : 'required_with:state_code';

        return [
            'state_code' => [$presence, 'integer', 'min:1'],
            'district_code' => [$dependent, 'nullable', 'integer', 'min:1'],
            'subdistrict_code' => [$dependent, 'nullable', 'string', 'max:20'],
            'subdistrict_name' => ['nullable', 'required_if:subdistrict_code,'.self::OTHER_SUBDISTRICT, 'string', 'max:150'],
            'city_village' => [$dependent, 'nullable', 'string', 'min:2', 'max:150'],
            'pincode' => [$dependent, 'nullable', 'regex:/^[1-9][0-9]{5}$/'],
        ];
    }

    public function attributes(array $input): array
    {
        if (blank($input['state_code'] ?? null)) {
            return [];
        }

        $stateCode = (int) $input['state_code'];
        $districtCode = (int) ($input['district_code'] ?? 0);
        $subdistrictCode = trim((string) ($input['subdistrict_code'] ?? ''));

        $stateName = $this->directory->states()[$stateCode] ?? null;
        if ($stateName === null) {
            throw ValidationException::withMessages(['state_code' => 'Please select a valid state.']);
        }

        $districtName = $this->directory->districts($stateCode)[$districtCode] ?? null;
        if ($districtName === null) {
            throw ValidationException::withMessages(['district_code' => 'Please select a district from the chosen state.']);
        }

        if ($subdistrictCode === self::OTHER_SUBDISTRICT) {
            $subdistrictName = trim((string) ($input['subdistrict_name'] ?? ''));
            $subdistrictCode = null;
        } else {
            $subdistrictName = $this->directory->subdistricts($districtCode)[$subdistrictCode] ?? null;

            if ($subdistrictName === null) {
                throw ValidationException::withMessages(['subdistrict_code' => 'Please select a taluka from the chosen district, or choose Other.']);
            }
        }

        return [
            'state_code' => $stateCode,
            'state_name' => $stateName,
            'district_code' => $districtCode,
            'district_name' => $districtName,
            'subdistrict_code' => $subdistrictCode,
            'subdistrict_name' => $subdistrictName,
            'city_village' => trim((string) ($input['city_village'] ?? '')),
            'pincode' => trim((string) ($input['pincode'] ?? '')),
        ];
    }
}
