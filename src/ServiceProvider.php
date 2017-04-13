<?php

namespace SoapBox\SignedRequests;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Publishes our ability to add a configuration file during boot.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfigurations();
    }

    /**
     * Adds our configuration file to the publishes array.
     *
     * @return void
     */
    protected function publishConfigurations()
    {
        $this->publishes([
            __DIR__.'/../resources/config/signed-requests.php' => config_path('signed-requests.php'),
        ]);
    }
}
