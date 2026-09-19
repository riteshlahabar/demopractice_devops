<?php

namespace App\Services\Auth\Firebase;

use App\Exceptions\Auth\InvalidPhoneCredentialException;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * SRP: holding Google's current signing keys for Firebase ID tokens.
 *
 * Google rotates these keys roughly daily, so they are fetched and cached
 * rather than shipped in the repository. Kept apart from the verifier because
 * "where do the keys come from" and "is this token valid" fail for different
 * reasons and are worth swapping independently.
 */
final class GoogleSecureTokenKeys
{
    public function __construct(private readonly CacheRepository $cache) {}

    /**
     * @return array<string, Key>
     */
    public function all(bool $forceRefresh = false): array
    {
        $cacheKey = (string) config('firebase.auth.jwks_cache_key', 'firebase.securetoken.jwks');

        if ($forceRefresh) {
            $this->cache->forget($cacheKey);
        }

        $jwks = $this->cache->remember(
            $cacheKey,
            (int) config('firebase.auth.jwks_cache_seconds', 3600),
            fn (): array => $this->fetch(),
        );

        try {
            return JWK::parseKeySet($jwks);
        } catch (Throwable $e) {
            // A malformed cached set would otherwise poison every login until
            // it expired on its own.
            $this->cache->forget($cacheKey);

            throw InvalidPhoneCredentialException::because('Phone sign-in keys are unavailable.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(): array
    {
        $url = (string) config('firebase.auth.jwks_url');
        $timeout = (int) config('firebase.auth.http_timeout_seconds', 8);

        try {
            $response = Http::timeout($timeout)->acceptJson()->get($url);
        } catch (Throwable $e) {
            throw InvalidPhoneCredentialException::because('Phone sign-in is temporarily unavailable.');
        }

        if (! $response->successful()) {
            throw InvalidPhoneCredentialException::because('Phone sign-in is temporarily unavailable.');
        }

        $jwks = $response->json();

        if (! is_array($jwks) || ! isset($jwks['keys']) || ! is_array($jwks['keys']) || $jwks['keys'] === []) {
            throw InvalidPhoneCredentialException::because('Phone sign-in is temporarily unavailable.');
        }

        return $jwks;
    }
}
