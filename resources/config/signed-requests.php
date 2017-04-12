<?php

array [
    /*
    |--------------------------------------------------------------------------
    | Available header overrides
    |--------------------------------------------------------------------------
    |
    | This allows you to customize the http headers that will be inspected to
    | look for the signature and algorithm respectively.
    */
    'headers' => [
        'signature' => 'X-Signature',
        'algorithm' => 'X-Algorithm'
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
    'key' => env('SIGNED_REQUEST_KEY', 'key')
];
