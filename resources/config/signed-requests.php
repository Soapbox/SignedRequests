<?php

return [
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
    | Allows the application to disable / enable request replay's
    |--------------------------------------------------------------------------
    |
    | If set to false, request id's will only be accepted once within the
    | configured tolerance window.
    */
    'allow-replays' => env('SIGNED_REQUEST_ALLOW_REPLAYS', false),

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
        'algorithm' => env('SIGNED_REQUEST_ALGORITHM_HEADER', 'X-Algorithm')
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
    | Allows a variance in time between the signing and verifiy servers
    |--------------------------------------------------------------------------
    |
    | This defines the number of seconds a request will be considered for
    | processing.
    */
    'tolerance' => env('SIGNED_REQUEST_TOLERANCE_SECONDS', 60)
];
