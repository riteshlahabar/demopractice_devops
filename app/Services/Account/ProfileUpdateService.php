<?php

namespace App\Services\Account;

use App\Contracts\Account\ProfileUpdateContract;
use App\Contracts\Files\PublicUploadContract;
use App\Contracts\Localization\SupportedLocalesContract;
use App\Contracts\Location\UserLocationContract;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

final class ProfileUpdateService implements ProfileUpdateContract
{
    public const PHOTO_DIRECTORY = 'uploads/profile-photos';

    /** @deprecated Kept for older callers; the live list comes from SupportedLocalesContract. */
    public const LANGUAGES = ['en', 'mr', 'hi'];

    public function __construct(
        private readonly PublicUploadContract $uploads,
        private readonly SupportedLocalesContract $locales,
        private readonly UserLocationContract $location,
    ) {}

    /**
     * @return array<int, string>
     */
    private function languageCodes(): array
    {
        return $this->locales->codes();
    }

    public function rules(User $user): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        return match ($user->role) {
            User::ROLE_DEALER => $rules + $this->location->rules() + [
                'firm_name' => ['required', 'string', 'max:255'],
                'gst_number' => ['nullable', 'string', 'max:20'],
            ],
            User::ROLE_CUSTOMER => $rules + $this->location->rules() + [
                'date_of_birth' => ['nullable', 'date', 'before:today'],
                'preferred_language' => ['nullable', Rule::in($this->languageCodes())],
            ],
            default => $rules,
        };
    }

    public function update(User $user, array $data): User
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        // A blank email keeps the stored one, so OTP accounts are not left without any.
        $user->forceFill(array_filter([
            'name' => trim((string) $data['name']),
            'email' => $email,
        ], fn (string $value) => $value !== '') + $this->location->attributes($data))->save();

        if ($user->role === User::ROLE_DEALER) {
            $user->dealerProfile?->update([
                'firm_name' => trim((string) $data['firm_name']),
                'gst_number' => $this->nullableText($data['gst_number'] ?? null),
            ]);
        }

        if ($user->role === User::ROLE_CUSTOMER) {
            CustomerProfile::query()->updateOrCreate(['user_id' => $user->id], [
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'preferred_language' => $data['preferred_language'] ?? null,
            ]);
        }

        return $user->fresh($this->relations($user)) ?? $user;
    }

    public function updatePhoto(User $user, UploadedFile $photo): User
    {
        $previous = $user->profile_photo;

        $user->forceFill(['profile_photo' => $this->uploads->store($photo, self::PHOTO_DIRECTORY)])->save();

        $this->uploads->delete($previous);

        return $user;
    }

    /**
     * Same relations the role's GET profile endpoint returns.
     *
     * @return list<string>
     */
    private function relations(User $user): array
    {
        return match ($user->role) {
            User::ROLE_DEALER => ['dealerProfile.salesman', 'addresses'],
            User::ROLE_CUSTOMER => ['customerProfile', 'addresses'],
            default => ['addresses'],
        };
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
