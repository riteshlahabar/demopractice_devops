<?php

namespace App\Contracts\Auth;

use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\InvalidPhoneCredentialException;

/**
 * SRP: turning a Firebase ID token into a phone number this server trusts.
 *
 * An interface because phone sign-in is the kind of thing that gets swapped —
 * a fake implementation is what makes the auth controllers testable without
 * reaching Google.
 */
interface FirebaseIdTokenContract
{
    /**
     * @throws InvalidPhoneCredentialException when the token is not a valid,
     *                                         current token for this project
     *                                         carrying a phone number.
     */
    public function verify(string $idToken): VerifiedPhone;
}
