<?php

namespace Tests\Unit;

use App\Contracts\Location\LocationDirectoryContract;
use App\Contracts\Location\UserLocationContract;
use App\Services\Location\UserLocationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserLocationServiceTest extends TestCase
{
    private function service(): UserLocationService
    {
        return new UserLocationService(new class implements LocationDirectoryContract
        {
            public function states(): array
            {
                return [27 => 'Maharashtra', 28 => 'Andhra Pradesh'];
            }

            public function districts(int $stateCode): array
            {
                return $stateCode === 27 ? [490 => 'Pune', 487 => 'Nashik'] : [];
            }

            public function subdistricts(int $districtCode): array
            {
                return $districtCode === 490 ? ['4200' => 'Indapur', '4193' => 'Haveli'] : [];
            }
        });
    }

    private function input(array $overrides = []): array
    {
        return $overrides + [
            'state_code' => '27', 'district_code' => '490', 'subdistrict_code' => '4200',
            'city_village' => ' Bhigwan ', 'pincode' => '413130',
        ];
    }

    public function test_a_valid_selection_becomes_codes_and_names(): void
    {
        $this->assertSame([
            'state_code' => 27, 'state_name' => 'Maharashtra',
            'district_code' => 490, 'district_name' => 'Pune',
            'subdistrict_code' => '4200', 'subdistrict_name' => 'Indapur',
            'city_village' => 'Bhigwan', 'pincode' => '413130',
        ], $this->service()->attributes($this->input()));
    }

    public function test_other_taluka_keeps_the_typed_name_without_a_code(): void
    {
        $attributes = $this->service()->attributes($this->input([
            'subdistrict_code' => UserLocationContract::OTHER_SUBDISTRICT, 'subdistrict_name' => ' Mulshi ',
        ]));

        $this->assertNull($attributes['subdistrict_code']);
        $this->assertSame('Mulshi', $attributes['subdistrict_name']);
    }

    public function test_a_district_from_another_state_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->attributes($this->input(['state_code' => '28']));
    }

    public function test_a_taluka_from_another_district_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->attributes($this->input(['district_code' => '487']));
    }

    public function test_required_rules_need_every_field_and_a_six_digit_pincode(): void
    {
        $rules = $this->service()->rules();

        $this->assertTrue(Validator::make([], $rules)->fails());
        $this->assertTrue(Validator::make($this->input(['pincode' => '41313']), $rules)->fails());
        $this->assertTrue(Validator::make($this->input(['subdistrict_code' => 'other']), $rules)->fails());
        $this->assertFalse(Validator::make($this->input(), $rules)->fails());
    }

    public function test_optional_location_left_blank_changes_nothing(): void
    {
        $service = $this->service();

        $this->assertFalse(Validator::make([], $service->rules(required: false))->fails());
        $this->assertSame([], $service->attributes([]));
    }
}
