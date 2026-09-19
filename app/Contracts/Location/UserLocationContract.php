<?php

namespace App\Contracts\Location;

use Illuminate\Validation\ValidationException;

/**
 * SRP: validating the registration location fields and turning them into the
 * users-table columns (codes plus names), shared by the apps' API, the
 * website sign-up and the admin People forms.
 */
interface UserLocationContract
{
    /** Taluka value meaning "not in the list — typed by hand". */
    public const OTHER_SUBDISTRICT = 'other';

    /** Request field names, in form order. */
    public const FIELDS = ['state_code', 'district_code', 'subdistrict_code', 'subdistrict_name', 'city_village', 'pincode'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(bool $required = true): array;

    /**
     * Users-table attributes for validated input. Returns [] when the location
     * is optional and was left empty.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     *
     * @throws ValidationException when the district is not in the state or the
     *                             taluka is not in the district
     */
    public function attributes(array $input): array;
}
