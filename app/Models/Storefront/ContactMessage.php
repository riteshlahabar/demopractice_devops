<?php

namespace App\Models\Storefront;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    public const STATUS_NEW = 'new';

    protected $fillable = ['user_id', 'name', 'email', 'phone', 'subject', 'message', 'status', 'ip_address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The contact form asks for two names; the list shows one. */
    public static function fullName(?string $firstName, ?string $lastName): string
    {
        return trim(trim((string) $firstName).' '.trim((string) $lastName));
    }
}
