<?php

return [
    /*
     * Verifying a Firebase ID token needs only the project id. The signature is
     * checked against Google's public keys, so no service account private key
     * is ever stored on the server — there is nothing here to leak.
     */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    'auth' => [
        // Google's JWKS for Firebase ID tokens.
        'jwks_url' => env(
            'FIREBASE_JWKS_URL',
            'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com'
        ),

        'jwks_cache_key' => 'firebase.securetoken.jwks',
        'jwks_cache_seconds' => (int) env('FIREBASE_JWKS_CACHE_SECONDS', 3600),

        // Clocks drift between the handset, Google and this server, so a small
        // window either side of exp/iat is tolerated.
        'leeway_seconds' => (int) env('FIREBASE_TOKEN_LEEWAY_SECONDS', 60),

        // A token that was minted long ago is refused even if it has not yet
        // expired, so a captured token has a short window of use.
        'max_age_seconds' => (int) env('FIREBASE_TOKEN_MAX_AGE_SECONDS', 3600),

        'http_timeout_seconds' => (int) env('FIREBASE_HTTP_TIMEOUT_SECONDS', 8),
    ],
];
