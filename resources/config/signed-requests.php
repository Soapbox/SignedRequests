<?php

return [
    'default' => [
        /*
        |--------------------------------------------------------------------------
        | The algorithm to sign the request with
        |--------------------------------------------------------------------------
        |
        | This is the algorithm we'll use to sign the request.
        */
        'algorithm' => env('SIGNED_REQUEST_ALGORITHM', 'sha256'),

        /*
        |--------------------------------------------------------------------------
        | The prefix to use for all of our cache values
        |--------------------------------------------------------------------------
        |
        | This is the prefix we'll use for all of our keys.
        */
        'cache-prefix' => env('SIGNED_REQUEST_CACHE_PREFIX', 'signed-requests'),

        /*
        |--------------------------------------------------------------------------
        | Available header overrides
        |--------------------------------------------------------------------------
        |
        | This allows you to customize the http headers that will be inspected to
        | look for the signature and algorithm respectively.
        */
        'headers' => [
            'signature' => env('SIGNED_REQUEST_SIGNATURE_HEADER', 'X-Signature'),
            'algorithm' => env('SIGNED_REQUEST_ALGORITHM_HEADER', 'X-Signature-Algorithm')
        ],

        /*
        |--------------------------------------------------------------------------
        | The key for signing requests for verification.
        |--------------------------------------------------------------------------
        |
        | This value is the key we'll use to verify signatures with. By default this
        | key is expected from the environment. You can change this behaviour,
        | however it is not recommended.
        */
        'key' => env('SIGNED_REQUEST_KEY', 'key'),

        /*
        |--------------------------------------------------------------------------
        | Allows the management and tolerance of request replay's
        |--------------------------------------------------------------------------
        |
        | This allows you to configure if the middleware should prevent the same
        | request being replayed to your application, and adjust the tolerance
        | for request expiry.
        */
        'request-replay' => [
            'allow' => env('SIGNED_REQUEST_ALLOW_REPLAYS', false),
            'tolerance' => env('SIGNED_REQUEST_TOLERANCE_SECONDS', 30)
        ]
    ],
    'custom' => [
        'algorithm' => env('CUSTOM_SIGNED_REQUEST_ALGORITHM', 'sha256'),
        'cache-prefix' => env('CUSTOM_SIGNED_REQUEST_CACHE_PREFIX', 'signed-requests'),
        'headers' => [
            'signature' => env('CUSTOM_SIGNED_REQUEST_SIGNATURE_HEADER', 'X-Signature'),
            'algorithm' => env('CUSTOM_SIGNED_REQUEST_ALGORITHM_HEADER', 'X-Signature-Algorithm')
        ],
        'key' => env('CUSTOM_SIGNED_REQUEST_KEY', 'key'),
        'request-replay' => [
            'allow' => env('CUSTOM_SIGNED_REQUEST_ALLOW_REPLAYS', false),
            'tolerance' => env('CUSTOM_SIGNED_REQUEST_TOLERANCE_SECONDS', 30)
        ]
    ]
];
