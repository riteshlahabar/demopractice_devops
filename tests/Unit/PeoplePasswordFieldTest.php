<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Admin\Modules\ModuleValidation;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PeoplePasswordFieldTest extends TestCase
{
    public function test_people_forms_have_a_confirmed_password_that_is_optional_when_editing(): void
    {
        $validation = new ModuleValidation;
        $existing = new User;
        $existing->id = 7;

        foreach (['dealers', 'customers', 'salesmen'] as $key) {
            $module = config('admin.modules.'.$key);
            $createRules = $validation->rules($module)['password'] ?? null;
            $editRules = $validation->rules($module, $existing)['password'] ?? null;

            $this->assertIsArray($createRules, $key.' has no password field');
            $this->assertContains('confirmed', $createRules, $key);
            $this->assertNotContains('required', $editRules, $key);
            $this->assertSame('nullable', $editRules[0], $key);

            $this->assertTrue(Validator::make(['password' => null], ['password' => $editRules])->passes(), $key.' blank edit');
            $this->assertTrue(Validator::make(['password' => 'secret123', 'password_confirmation' => 'wrong'], ['password' => $editRules])->fails(), $key.' mismatch');
        }

        $this->assertContains('required', $validation->rules(config('admin.modules.salesmen'))['password']);
        $this->assertNotContains('required', $validation->rules(config('admin.modules.dealers'))['password']);
    }
}
