<?php

namespace App\Services\Auth\Firebase;

use App\Contracts\Auth\FirebaseIdTokenContract;
use App\Data\Auth\VerifiedPhone;
use App\Exceptions\Auth\InvalidPhoneCredentialException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;
use UnexpectedValueException;

/**
 * Verifies a Firebase ID token the way Google documents it.
 *
 * The signature check alone is not enough: a token signed by Google but minted
 * for someone else's Firebase project would otherwise be accepted, which is
 * why the audience and issuer are checked against our own project id.
 */
final class FirebaseIdTokenService implements FirebaseIdTokenContract
{
    private const ISSUER_PREFIX = 'https://securetoken.google.com/';

    public function __construct(private readonly GoogleSecureTokenKeys $keys) {}

    public function verify(string $idToken): VerifiedPhone
    {
        $projectId = trim((string) config('firebase.project_id', ''));

        if ($projectId === '') {
            Log::error('FIREBASE_PROJECT_ID is not set; phone sign-in cannot be verified.');

            throw InvalidPhoneCredentialException::because('Phone sign-in is not configured.');
        }

        JWT::$leeway = (int) config('firebase.auth.leeway_seconds', 60);

        $claims = $this->decode(trim($idToken));

        $this->assertClaims($claims, $projectId);

        $phone = VerifiedPhone::fromFirebase(
            (string) ($claims->phone_number ?? ''),
            (string) ($claims->sub ?? ''),
        );

        if (! $phone->isValid()) {
            $this->reject('phone_number claim is missing or not a ten digit number');
        }

        return $phone;
    }

    /**
     * Google rotates signing keys, so a token signed with a key we have not
     * cached yet is retried once against a fresh key set before it is refused.
     */
    private function decode(string $idToken): stdClass
    {
        if ($idToken === '') {
            $this->reject('empty token');
        }

        try {
            return JWT::decode($idToken, $this->keys->all());
        } catch (SignatureInvalidException|UnexpectedValueException $e) {
            try {
                return JWT::decode($idToken, $this->keys->all(forceRefresh: true));
            } catch (Throwable $retry) {
                $this->reject($retry->getMessage());
            }
        } catch (InvalidPhoneCredentialException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->reject($e->getMessage());
        }
    }

    private function assertClaims(stdClass $claims, string $projectId): void
    {
        if (($claims->aud ?? null) !== $projectId) {
            $this->reject('aud does not match this project');
        }

        if (($claims->iss ?? null) !== self::ISSUER_PREFIX.$projectId) {
            $this->reject('iss does not match this project');
        }

        if (trim((string) ($claims->sub ?? '')) === '') {
            $this->reject('sub is empty');
        }

        $now = time();
        $leeway = (int) config('firebase.auth.leeway_seconds', 60);
        $maxAge = (int) config('firebase.auth.max_age_seconds', 3600);

        $authTime = (int) ($claims->auth_time ?? 0);

        if ($authTime > $now + $leeway) {
            $this->reject('auth_time is in the future');
        }

        $issuedAt = (int) ($claims->iat ?? 0);

        if ($issuedAt <= 0 || $issuedAt < $now - $maxAge - $leeway) {
            $this->reject('token is older than the accepted window');
        }
    }

    /**
     * The caller is told only that verification failed. Which check failed is
     * useful to us and to nobody else.
     */
    private function reject(string $reason): never
    {
        Log::warning('Firebase ID token rejected.', ['reason' => $reason]);

        throw InvalidPhoneCredentialException::because('Phone verification failed. Please sign in again.');
    }
}
