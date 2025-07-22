<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Keycloak Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Keycloak authentication integration
    |
    */

    'server_url' => env('KEYCLOAK_SERVER_URL', 'http://localhost:8080/auth'),

    'realm' => env('KEYCLOAK_REALM', 'master'),

    'client_id' => env('KEYCLOAK_CLIENT_ID'),

    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),

    'username' => env('KEYCLOAK_ADMIN_USERNAME'),

    'password' => env('KEYCLOAK_ADMIN_PASSWORD'),

    'redirect_uri' => env('KEYCLOAK_REDIRECT_URI', env('APP_URL') . '/auth/callback'),

    'cache_user_info' => env('KEYCLOAK_CACHE_USER_INFO', true),

    'cache_duration' => env('KEYCLOAK_CACHE_DURATION', 300), // 5 minutes
];