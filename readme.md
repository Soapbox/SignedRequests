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
SIGNED_REQUEST_SIGNATURE_HEADER=
SIGNED_REQUEST_ALGORITHM_HEADER=
SIGNED_REQUEST_KEY=
```

The `SIGNED_REQUEST_SIGNATURE_HEADER` should be the request header that the signature will be included on, something like `X-SIGNATURE`. Similarly the `SIGNED_REQUEST_ALGORITHM_HEADER` should be the request header that the includes the algorithm used to sign the request. Finally the `SIGNED_REQUEST_KEY` should hold the key used to verify the signed requests.

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
