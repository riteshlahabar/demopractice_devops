<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leave entitlements
    |--------------------------------------------------------------------------
    |
    | Days granted per leave type for a full calendar year. The salesman app
    | reads the remaining balance from these, so HR can revise policy here
    | without a code change.
    |
    */
    'leave_entitlements' => [
        'casual' => (int) env('HRMS_LEAVE_CASUAL', 12),
        'sick' => (int) env('HRMS_LEAVE_SICK', 8),
        'earned' => (int) env('HRMS_LEAVE_EARNED', 15),
        'unpaid' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Advances and loans
    |--------------------------------------------------------------------------
    |
    | How many advance/loan requests an employee may have outstanding at once.
    |
    */
    'max_open_advances' => (int) env('HRMS_MAX_OPEN_ADVANCES', 2),
];
