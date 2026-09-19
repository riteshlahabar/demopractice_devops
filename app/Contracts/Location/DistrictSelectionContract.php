<?php

namespace App\Contracts\Location;

use Illuminate\Validation\ValidationException;

/**
 * SRP: validating a State + District pair and resolving it to codes plus
 * names from the LGD directory.
 */
interface DistrictSelectionContract
{
    /** Request field names. */
    public const FIELDS = ['state_code', 'district_code'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array;

    /**
     * @param  array<string, mixed>  $input
     * @return array{state_code: int, state_name: string, district_code: int, district_name: string}
     *
     * @throws ValidationException when the district is not in the chosen state
     */
    public function attributes(array $input): array;
}
