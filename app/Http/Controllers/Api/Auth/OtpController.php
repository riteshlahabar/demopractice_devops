<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Auth\OtpContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class OtpController extends AuthApiController
{
    public function __construct(private readonly OtpContract $otp) {}

    public function request(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'purpose' => ['required', Rule::in(['customer_login', 'dealer_login'])],
        ]);

        $issued = $this->otp->issue($validated['mobile'], $validated['purpose']);

        return $this->success([
            'mobile' => $validated['mobile'],
            'expires_in_seconds' => 600,
            'debug_otp' => $issued['debug_code'],
        ], 'OTP generated.');
    }
}
