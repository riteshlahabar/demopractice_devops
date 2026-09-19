<?php

namespace App\Contracts\Account;

use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Editing the signed-in app user's own profile and profile photo.
 */
interface ProfileUpdateContract
{
    /**
     * Validation rules for the fields this user's role may edit. Mobile is
     * never editable here: it is the verified login identity.
     *
     * @return array<string, mixed>
     */
    public function rules(User $user): array;

    /**
     * @param  array<string, mixed>  $data  Validated input.
     */
    public function update(User $user, array $data): User;

    /**
     * Stores the new photo and deletes the previous one.
     */
    public function updatePhoto(User $user, UploadedFile $photo): User;
}
