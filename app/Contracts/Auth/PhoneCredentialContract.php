<?php

namespace App\Contracts\Auth;

use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\InvalidPhoneCredentialException;

/**
 * SRP: deciding which proof of phone ownership a request carries.
 *
 * Callers hand over the raw credential and get back a verified number, so the
 * controllers never learn whether the proof was a Firebase token or the
 * test-number bypass.
 */
interface PhoneCredentialContract
{
    /**
     * @param  array<string, mixed>  $credential
     *
     * @throws InvalidPhoneCredentialException
     */
    public function verify(array $credential, string $purpose): VerifiedPhone;
}
