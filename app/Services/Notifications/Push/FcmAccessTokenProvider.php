<?php

namespace App\Services\Notifications\Push;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OAuth access token for the FCM HTTP v1 API, signed with the Firebase
 * service-account key (firebase/php-jwt is already installed, so no Google
 * SDK is needed). Tokens last an hour; one is cached for 50 minutes.
 */
class FcmAccessTokenProvider
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    public function token(): string
    {
        return Cache::remember('fcm.access_token', now()->addMinutes(50), function (): string {
            $account = $this->serviceAccount();
            $now = time();

            $assertion = JWT::encode([
                'iss' => $account['client_email'],
                'scope' => self::SCOPE,
                'aud' => $account['token_uri'],
                'iat' => $now,
                'exp' => $now + 3600,
            ], $account['private_key'], 'RS256');

            $response = Http::asForm()
                ->timeout((int) config('notifications.push.fcm.timeout', 10))
                ->post($account['token_uri'], [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $assertion,
                ]);

            $token = $response->json('access_token');
            if (! $response->successful() || ! is_string($token)) {
                throw new RuntimeException('FCM access token request failed: HTTP '.$response->status());
            }

            return $token;
        });
    }

    /**
     * @return array{client_email: string, private_key: string, token_uri: string}
     */
    private function serviceAccount(): array
    {
        $path = (string) config('notifications.push.fcm.credentials');
        if ($path === '' || ! is_readable($path)) {
            throw new RuntimeException('FIREBASE_CREDENTIALS is not set or the file cannot be read.');
        }

        $json = json_decode((string) file_get_contents($path), true);
        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new RuntimeException('FIREBASE_CREDENTIALS is not a valid service-account JSON file.');
        }

        return [
            'client_email' => (string) $json['client_email'],
            'private_key' => (string) $json['private_key'],
            'token_uri' => (string) ($json['token_uri'] ?? 'https://oauth2.googleapis.com/token'),
        ];
    }
}
