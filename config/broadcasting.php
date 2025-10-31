<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "reverb", "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over WebSockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST', '0.0.0.0'),
                'port' => env('REVERB_PORT', 6001),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
                'verify' => env('PUSHER_VERIFY_SSL', false),
                'timeout' => 30, // Timeout de connexion augmenté
                'connect_timeout' => 10, // Timeout de handshake augmenté
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => env('PUSHER_VERIFY_SSL', false),
                    CURLOPT_SSL_VERIFYHOST => env('PUSHER_VERIFY_SSL', false) ? 2 : 0,
                    // Forcer TLS 1.2 minimum mais permettre TLS 1.3
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    // Spécifier les cipher suites compatibles OpenSSL 3.x
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                    // Timeouts pour éviter les blocages
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ],
            ],
        ],

        'soketi' => [
            'driver' => 'pusher',
            'key' => env('SOKETI_APP_KEY', 'app-key'),
            'secret' => env('SOKETI_APP_SECRET', 'app-secret'),
            'app_id' => env('SOKETI_APP_ID', 'app-id'),
            'options' => [
                'host' => env('SOKETI_HOST', '127.0.0.1'),
                'port' => env('SOKETI_PORT', 6001),
                'scheme' => env('SOKETI_SCHEME', 'http'),
                'encrypted' => false,
                'useTLS' => env('SOKETI_SCHEME', 'http') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
