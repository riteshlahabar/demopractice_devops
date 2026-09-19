<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Http\Controllers\Admin\Concerns\PeopleModuleController;
use App\Models\CustomerProfile;
use App\Models\User;

class CustomerController extends PeopleModuleController
{
    protected string $moduleKey = 'customers';

    protected string $role = User::ROLE_CUSTOMER;

    protected string $profileRelation = 'customerProfile';

    protected string $profileModel = CustomerProfile::class;

    protected array $profileFields = ['date_of_birth', 'preferred_language'];
}
