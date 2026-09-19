<?php

namespace App\Services\Location;

use App\Contracts\Location\DistrictSelectionContract;
use App\Contracts\Location\LocationDirectoryContract;
use Illuminate\Validation\ValidationException;

final class DistrictSelectionService implements DistrictSelectionContract
{
    public function __construct(private readonly LocationDirectoryContract $directory) {}

    public function rules(): array
    {
        return [
            'state_code' => ['required', 'integer', 'min:1'],
            'district_code' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(array $input): array
    {
        $stateCode = (int) ($input['state_code'] ?? 0);
        $districtCode = (int) ($input['district_code'] ?? 0);

        $stateName = $this->directory->states()[$stateCode] ?? null;
        if ($stateName === null) {
            throw ValidationException::withMessages(['state_code' => 'Please select a valid state.']);
        }

        $districtName = $this->directory->districts($stateCode)[$districtCode] ?? null;
        if ($districtName === null) {
            throw ValidationException::withMessages(['district_code' => 'Please select a district from the chosen state.']);
        }

        return [
            'state_code' => $stateCode,
            'state_name' => $stateName,
            'district_code' => $districtCode,
            'district_name' => $districtName,
        ];
    }
}
