<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Str;

abstract class AuthApiController extends ApiController
{
    /**
     * Mobile-first signups have no email address, but users.email is required
     * and unique, so a placeholder derived from the number is stored instead.
     */
    protected function virtualEmail(string $mobile, string $role): string
    {
        $clean = preg_replace('/\D+/', '', $mobile) ?: Str::lower(Str::random(10));

        return $clean.'@'.$role.'.bawaskar.local';
    }
}
