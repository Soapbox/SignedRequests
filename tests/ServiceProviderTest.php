<?php

namespace Tests;

use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Base;
use SoapBox\SignedRequests\ServiceProvider;

class ServiceProviderTest extends Base
{
    /**
     * @test
     */
    public function it_publishes_configurations_when_it_is_booted()
    {
        $provider = new TestServiceProvider(app());
        $provider->boot();

        $publishes = $provider->getPublishes()[TestServiceProvider::class];

        $this->assertNotEmpty($publishes);

        foreach ($publishes as $key => $value) {
            $this->assertTrue(Str::endsWith($key, 'signed-requests.php'));
            $this->assertTrue(Str::endsWith($value, 'signed-requests.php'));
        }
    }
}

class TestServiceProvider extends ServiceProvider
{
    public function getPublishes()
    {
        return static::$publishes;
    }
}
