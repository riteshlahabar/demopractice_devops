<?php

namespace App\Services\Hr;

use App\Contracts\Hr\LeavePolicyContract;
use App\Models\Hr\LeavePolicy;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Reads the Leave Policies master. The old hardcoded entitlements in
 * config/hrms.php stay as the fallback, so the salesman app keeps working if
 * the table is empty or the database is unreachable.
 */
class LeavePolicyService implements LeavePolicyContract
{
    private const CACHE_KEY = 'hrms.leave_policies';

    private const CACHE_MINUTES = 60;

    public function entitlements(): array
    {
        $policies = $this->policies();

        if ($policies === []) {
            return array_map(static fn ($days): float => (float) $days, config('hrms.leave_entitlements', []));
        }

        return array_map(static fn (array $policy): float => (float) $policy['annual_days'], $policies);
    }

    public function paidTypes(): array
    {
        $policies = $this->policies();

        if ($policies === []) {
            return array_keys(array_filter(config('hrms.leave_entitlements', []), static fn ($days): bool => (float) $days > 0));
        }

        return array_keys(array_filter($policies, static fn (array $policy): bool => (bool) $policy['is_paid']));
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, array{annual_days: float, is_paid: bool}>
     */
    private function policies(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_MINUTES), static function (): array {
                return LeavePolicy::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->mapWithKeys(static fn (LeavePolicy $policy): array => [
                        $policy->leave_type => ['annual_days' => (float) $policy->annual_days, 'is_paid' => (bool) $policy->is_paid],
                    ])
                    ->all();
            });
        } catch (Throwable) {
            return [];
        }
    }
}
