<?php

namespace App\Services\Storefront;

use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Contracts\Storefront\StorefrontContactContract;
use App\Models\Storefront\ContactMessage;
use Illuminate\Http\Request;

final class StorefrontContactService implements StorefrontContactContract
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity
    ) {}

    public function store(Request $request, array $data): ContactMessage
    {
        return ContactMessage::query()->create([
            'user_id' => $this->identity->user($request)?->getKey(),
            'name' => ContactMessage::fullName($data['first_name'] ?? null, $data['last_name'] ?? null),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'status' => ContactMessage::STATUS_NEW,
            'ip_address' => $request->ip(),
        ]);
    }
}
