<?php

$configuredOrigins = array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:4200,http://127.0.0.1:4200'))
));

// Capacitor serves native Android/iOS applications from a local origin. These
// are not public web origins, but must be permitted for the bearer-token API.
$nativeOrigins = ['http://localhost', 'https://localhost', 'capacitor://localhost'];

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Ionic/Angular development runs on port 4200 while Laravel runs on port
    | 8000. Explicitly allow those development origins so browser preflight
    | requests can reach the API.
    |
    */
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique([...$configuredOrigins, ...$nativeOrigins])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Authentication uses a Bearer token, not a cross-site cookie.
    'supports_credentials' => false,
];
