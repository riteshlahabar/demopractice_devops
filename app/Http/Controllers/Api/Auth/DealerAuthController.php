<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Auth\OtpContract;
use App\Contracts\Auth\PhoneCredentialContract;
use App\Contracts\Auth\RegistrationTokenContract;
use App\Data\Auth\VerifiedPhone;
use App\Models\User;
use App\Services\Auth\DealerAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class DealerAuthController extends DealerAuthApiController
{
    private const PURPOSE = 'dealer_login';

    public function __construct(
        private readonly OtpContract $otp,
        private readonly PhoneCredentialContract $phoneCredential,
        private readonly DealerAccountService $accounts,
        private readonly RegistrationTokenContract $registrationTokens,
    ) {}

    /**
     * Firebase phone sign-in. The number comes from inside the verified ID
     * token, so the firm details in the body cannot be attached to a mobile
     * the caller does not own.
     */
    public function verifyFirebase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['nullable', 'string', 'max:4096'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'otp' => ['nullable', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
            'firm_name' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
        ]);

        $phone = $this->phoneCredential->verify($validated, self::PURPOSE);

        return $this->completeSignIn($phone, $validated);
    }

    /**
     * @deprecated Superseded by verifyFirebase(); kept while the dealer app is
     *             migrated, and removed once no old build is in the field.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
            'firm_name' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
        ]);

        if (! $this->otp->verify($validated['mobile'], self::PURPOSE, $validated['otp'])) {
            return $this->fail('Invalid or expired OTP.', 422);
        }

        return $this->completeSignIn(VerifiedPhone::fromOtp($validated['mobile']), $validated);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->with('dealerProfile.salesman')
            ->where('email', $validated['email'])
            ->where('role', User::ROLE_DEALER)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->fail('Invalid dealer login.', 401);
        }

        if (! $this->accounts->isApproved($user)) {
            return $this->fail('Dealer approval is pending.', 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->success([
            'user' => $user,
            'token' => $user->createApiToken('dealer-app'),
        ], 'Dealer logged in.');
    }

    /**
     * A registered dealer is signed in. A new number is not an error: it gets
     * a registration token so the app can open the firm-details form next.
     *
     * @param  array<string, mixed>  $details
     */
    private function completeSignIn(VerifiedPhone $phone, array $details): JsonResponse
    {
        if ($this->accounts->needsRegistration($phone, $details)) {
            return $this->success([
                'registration_required' => true,
                'registration_token' => $this->registrationTokens->issue($phone, RegistrationTokenContract::DEALER_REGISTRATION),
                'expires_in' => $this->registrationTokens->lifetimeSeconds(),
                'mobile' => $phone->mobile,
            ], 'Mobile verified. Complete dealer registration.');
        }

        $user = $this->accounts->register($phone, $details);

        return $this->respondToDealer($user, $this->accounts->isApproved($user));
    }
}
