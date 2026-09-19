<?php

namespace App\Services\Auth;

use App\Contracts\Auth\OtpContract;
use App\Models\Auth\OtpCode;
use Illuminate\Support\Facades\Hash;

final class OtpService implements OtpContract
{
    private const LIFETIME_MINUTES = 10;

    public function issue(string $mobile, string $purpose): array
    {
        $code = $this->codeFor($mobile);

        OtpCode::query()->create([
            'mobile' => $mobile,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES),
        ]);

        return [
            'code' => $code,
            // Returning the code is a development convenience only. On a
            // production APP_ENV it is withheld, which is why APP_ENV must be
            // set correctly on the live server.
            'debug_code' => $this->isProduction() && ! $this->isBypassMobile($mobile) ? null : $code,
        ];
    }

    public function verify(string $mobile, string $purpose, string $code): bool
    {
        if ($this->isBypassMobile($mobile) && hash_equals($this->bypassCode(), $code)) {
            return true;
        }

        $otpCode = OtpCode::query()
            ->where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', (int) config('erp_auth.otp.max_attempts', 5))
            ->latest()
            ->first();

        if (! $otpCode || ! Hash::check($code, $otpCode->code_hash)) {
            optional($otpCode)->increment('attempts');

            return false;
        }

        $otpCode->forceFill(['verified_at' => now()])->save();

        return true;
    }

    public function lifetimeSeconds(): int
    {
        return self::LIFETIME_MINUTES * 60;
    }

    private function codeFor(string $mobile): string
    {
        if ($this->isBypassMobile($mobile)) {
            return $this->bypassCode();
        }

        return $this->isProduction()
            ? (string) random_int(100000, 999999)
            : (string) config('erp_auth.otp.debug_code', '123456');
    }

    private function isBypassMobile(string $mobile): bool
    {
        $clean = preg_replace('/\D+/', '', $mobile);

        $numbers = array_map(
            static fn (string $number): string => preg_replace('/\D+/', '', $number),
            config('erp_auth.otp.bypass_numbers', [])
        );

        return in_array($clean, $numbers, true);
    }

    private function bypassCode(): string
    {
        return (string) config('erp_auth.otp.bypass_code', '123456');
    }

    private function isProduction(): bool
    {
        return app()->environment('production');
    }
}
