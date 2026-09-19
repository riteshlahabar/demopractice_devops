<?php

return [
    'otp' => [
        'bypass_numbers' => array_values(array_filter(array_map(
            static fn (string $mobile): string => trim($mobile),
            explode(',', env('OTP_BYPASS_NUMBERS', ''))
        ))),
        'bypass_code' => env('OTP_BYPASS_CODE', '123456'),
        'debug_code' => env('OTP_DEBUG_CODE', '123456'),

        // A six digit code is brute forceable, so a code is burnt after this
        // many wrong guesses regardless of how long it has left to live.
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    ],

    'token' => [
        'lifetime_days' => (int) env('API_TOKEN_LIFETIME_DAYS', 30),
    ],
];
