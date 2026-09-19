<?php

namespace App\Services\Auth;

use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\AccountRoleConflictException;
use App\Exceptions\Auth\DealerNotRegisteredException;
use App\Models\DealerProfile;
use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Support\Str;

/**
 * SRP: turning a verified phone number plus firm details into a dealer record.
 *
 * A dealer is created in pending_approval and stays there: whether the account
 * may actually sign in is an admin decision, checked separately by the caller.
 */
final class DealerAccountService
{
    /**
     * Whether this number still has to go through the registration form: no
     * dealer (or firm profile) exists yet and the request carried no details.
     *
     * @param  array<string, mixed>  $details
     */
    public function needsRegistration(VerifiedPhone $phone, array $details = []): bool
    {
        return $this->missingDetails($this->findDealer($phone), $details);
    }

    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>  $location  users-table location columns (UserLocationContract::attributes)
     */
    public function register(VerifiedPhone $phone, array $details, array $location = []): User
    {
        $existing = $this->findDealer($phone);

        if ($this->missingDetails($existing, $details)) {
            throw DealerNotRegisteredException::make();
        }

        $name = $this->detail($details, 'name');

        $user = $existing ?: User::query()->create([
            'name' => $name,
            'mobile' => $phone->mobile,
            'email' => $this->virtualEmail($phone->mobile),
            'password' => Str::password(32),
            'role' => User::ROLE_DEALER,
            'status' => 'pending_approval',
            'mobile_verified_at' => now(),
        ]);

        // Blank fields never overwrite stored details.
        $user->forceFill(array_filter([
            'name' => $name,
            'role' => User::ROLE_DEALER,
            'mobile_verified_at' => now(),
        ], fn ($value) => $value !== '') + $location)->save();

        DealerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            array_filter([
                'dealer_code' => 'DLR'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'firm_name' => $this->detail($details, 'firm_name'),
                'gst_number' => $this->detail($details, 'gst_number'),
            ], fn ($value) => $value !== '')
        );

        return $user->load('dealerProfile.salesman');
    }

    public function isApproved(User $user): bool
    {
        return $user->status === 'active' && $user->dealerProfile?->approved_at !== null;
    }

    private function findDealer(VerifiedPhone $phone): ?User
    {
        $existing = $this->findByMobile($phone);

        if ($existing && $existing->role !== User::ROLE_DEALER) {
            throw AccountRoleConflictException::forRole($existing->role);
        }

        return $existing;
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function missingDetails(?User $existing, array $details): bool
    {
        return (! $existing && $this->detail($details, 'name') === '')
            || (! $existing?->dealerProfile && $this->detail($details, 'firm_name') === '');
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function detail(array $details, string $key): string
    {
        return trim((string) ($details[$key] ?? ''));
    }

    private function findByMobile(VerifiedPhone $phone): ?User
    {
        $variants = MobileNumber::lookupVariants($phone->mobile);

        return $variants === []
            ? null
            : User::query()->whereIn('mobile', $variants)->first();
    }

    private function virtualEmail(string $mobile): string
    {
        return $mobile.'@dealer.bawaskar.local';
    }
}
