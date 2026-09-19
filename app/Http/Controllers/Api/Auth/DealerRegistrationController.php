<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Auth\RegistrationTokenContract;
use App\Contracts\Location\UserLocationContract;
use App\Services\Auth\DealerAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Second step of dealer sign-up: the mobile was verified by OTP and exchanged
 * for a registration token; this attaches the firm details to that number.
 */
final class DealerRegistrationController extends DealerAuthApiController
{
    public function __construct(
        private readonly RegistrationTokenContract $registrationTokens,
        private readonly DealerAccountService $accounts,
        private readonly UserLocationContract $location,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'registration_token' => ['required', 'string', 'max:4096'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'firm_name' => ['required', 'string', 'min:2', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
        ] + $this->location->rules());

        $phone = $this->registrationTokens->resolve(
            $validated['registration_token'],
            RegistrationTokenContract::DEALER_REGISTRATION,
        );

        if (! $phone) {
            return $this->fail('Mobile verification has expired. Please verify your mobile number again.', 422);
        }

        $user = $this->accounts->register($phone, $validated, $this->location->attributes($validated));

        return $this->respondToDealer($user, $this->accounts->isApproved($user));
    }
}
