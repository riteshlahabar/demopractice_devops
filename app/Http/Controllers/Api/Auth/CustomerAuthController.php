<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Auth\OtpContract;
use App\Contracts\Auth\PhoneCredentialContract;
use App\Contracts\Location\UserLocationContract;
use App\Data\Auth\VerifiedPhone;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\Auth\CustomerAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CustomerAuthController extends AuthApiController
{
    private const PURPOSE = 'customer_login';

    public function __construct(
        private readonly OtpContract $otp,
        private readonly PhoneCredentialContract $phoneCredential,
        private readonly CustomerAccountService $accounts,
        private readonly UserLocationContract $location,
    ) {}

    /**
     * Firebase phone sign-in. The app proves the number with Google and posts
     * the resulting ID token; the number is read from inside that token, never
     * from the request body, so a caller cannot claim someone else's mobile.
     */
    public function verifyFirebase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['nullable', 'string', 'max:4096'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'otp' => ['nullable', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $phone = $this->phoneCredential->verify($validated, self::PURPOSE);

        return $this->issueToken($this->accounts->activate($phone, $validated['name'] ?? null));
    }

    /**
     * @deprecated Superseded by verifyFirebase(); kept while the customer app
     *             is migrated, and removed once no old build is in the field.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $this->otp->verify($validated['mobile'], self::PURPOSE, $validated['otp'])) {
            return $this->fail('Invalid or expired OTP.', 422);
        }

        $phone = VerifiedPhone::fromOtp($validated['mobile']);

        return $this->issueToken($this->accounts->activate($phone, $validated['name'] ?? null));
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ] + $this->location->rules());

        $location = $this->location->attributes($validated);
        $user = User::query()->where('mobile', $validated['mobile'])->first();
        $email = $validated['email'] ?? null;

        if ($user && $user->role !== User::ROLE_CUSTOMER) {
            return $this->fail('This mobile number is already registered for another account type.', 422);
        }

        $user = $user
            ? $this->updateExisting($user, $validated, $email)
            : $this->createNew($validated, $email);

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $user->forceFill($location)->save();
        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        return $this->success(['user' => $user->load('customerProfile')], 'Customer registered. Verify OTP to continue.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->where('role', User::ROLE_CUSTOMER)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password) || $user->status !== 'active') {
            return $this->fail('Invalid customer login.', 401);
        }

        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);
        $user->forceFill(['last_login_at' => now()])->save();

        return $this->issueToken($user->load('customerProfile'));
    }

    private function issueToken(User $user): JsonResponse
    {
        return $this->success([
            'user' => $user,
            'token' => $user->createApiToken('customer-app'),
        ], 'Customer logged in.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createNew(array $validated, ?string $email): User|JsonResponse
    {
        if ($email && User::query()->where('email', $email)->exists()) {
            return $this->fail('This email address is already registered.', 422);
        }

        return User::query()->create([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'email' => $email ?: $this->virtualEmail($validated['mobile'], 'customer'),
            'password' => $validated['password'] ?? Str::password(32),
            'role' => User::ROLE_CUSTOMER,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function updateExisting(User $user, array $validated, ?string $email): User|JsonResponse
    {
        $updates = ['name' => $validated['name'], 'status' => 'active'];

        if ($email && $email !== $user->email) {
            if (User::query()->where('email', $email)->whereKeyNot($user->id)->exists()) {
                return $this->fail('This email address is already registered.', 422);
            }

            $updates['email'] = $email;
        }

        if (! empty($validated['password'])) {
            $updates['password'] = $validated['password'];
        }

        $user->forceFill($updates)->save();

        return $user;
    }
}
