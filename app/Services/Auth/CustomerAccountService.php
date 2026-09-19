<?php

namespace App\Services\Auth;

use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\AccountRoleConflictException;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Support\Str;

/**
 * SRP: turning a verified phone number into an active customer account.
 *
 * Shared by the Firebase and the legacy OTP entry points so that "what a
 * customer login creates" is decided once, no matter how the number was
 * proved.
 */
final class CustomerAccountService
{
    public function activate(VerifiedPhone $phone, ?string $name = null): User
    {
        $existing = $this->findByMobile($phone);

        if ($existing && $existing->role !== User::ROLE_CUSTOMER) {
            throw AccountRoleConflictException::forRole($existing->role);
        }

        $user = $existing ?: User::query()->create([
            'name' => $name ?: 'Customer',
            'mobile' => $phone->mobile,
            'email' => $this->virtualEmail($phone->mobile),
            'password' => Str::password(32),
            'role' => User::ROLE_CUSTOMER,
            'status' => 'active',
            'mobile_verified_at' => now(),
        ]);

        $user->forceFill([
            'mobile_verified_at' => now(),
            'last_login_at' => now(),
            'status' => 'active',
        ])->save();

        CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        return $user->load('customerProfile');
    }

    /**
     * Legacy rows were written straight from the app, so the same person may be
     * stored as 9876543210, 919876543210 or +919876543210. All three are tried
     * rather than assuming one format and creating a duplicate account.
     */
    private function findByMobile(VerifiedPhone $phone): ?User
    {
        $variants = MobileNumber::lookupVariants($phone->mobile);

        return $variants === []
            ? null
            : User::query()->whereIn('mobile', $variants)->first();
    }

    private function virtualEmail(string $mobile): string
    {
        return $mobile.'@customer.bawaskar.local';
    }
}
