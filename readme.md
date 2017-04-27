# Signed Requests

A wrapper to add the ability to accept signed requests to a Laravel project.

## Installation

### Composer

```sh
composer require soapbox/signed-requests
```

### Setup the Service Provider

Open `config/app.php` and register the required service provider above your application providers.

```php
'providers' => [
    ...
    SoapBox\SignedRequests\ServiceProvider::class
    ...
]
```

### Publish the Configuration

```php
php artisan vendor:publish
```

### Configuring your Environment

You will need to set the following details in your environment:

```sh
SIGNED_REQUEST_ALGORITHM=
SIGNED_REQUEST_CACHE_PREFIX=
SIGNED_REQUEST_SIGNATURE_HEADER=
SIGNED_REQUEST_ALGORITHM_HEADER=
SIGNED_REQUEST_KEY=
SIGNED_REQUEST_ALLOW_REPLAYS=
SIGNED_REQUEST_TOLERANCE_SECONDS=
```

Each of the settings above allows for a different level of configuration.
    - `SIGNED_REQUEST_ALGORITHM` is the algorithm that will be used to generate / verify the signature. This is defaulted to use `sha256` feel free to change this to anything that `hash_hmac` accepts.
    - `SIGNED_REQUEST_CACHE_PREFIX` is the prefix to use for all the cache keys that will be generated. Here you can use the default if you're not planning on sharing a cache between multiple applications.
    - `SIGNED_REQUEST_SIGNATURE_HEADER` should be the request header that the signature will be included on, `X-Signature` will be used by default.
    - `SIGNED_REQUEST_ALGORITHM_HEADER` should be the request header that the includes the algorithm used to sign the request.
    - `SIGNED_REQUEST_KEY` is the shared secret key between the application generating the requests, and the application consuming them. This value should not be publically available.
    - `SIGNED_REQUEST_ALLOW_REPLAYS` allows you to enable or disable replay attacks. By default replays are disabled.
    - `SIGNED_REQUEST_TOLERANCE_SECONDS` is the number of seconds that a request will be considered for. This setting allows for some time drift between servers and is only used when replays are disabled.

### Setup the Middleware

Signed Requests includes a middleware to validate the signature of a request for your automatically. To get started, add the following middleware to the `$routeMiddleware` property of your `app/Http/Kernel.php` file.

```php
'verify-signature' => \SoapBox\SignedRequests\Middlewares\VerifySignature::class
```

### Verify the Signature

The `verify-signature` middleware may be assigned to a route to verify the signature of the incoming request to verify its authenticity:

```php
Route::get('/fire', function () {
    return "You'll only see this if the signature of the request is valid!";
})->middleware('verify-signature');
```
