<?php

namespace Tests\Middlewares\Laravel;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Cache\ArrayStore;
use SoapBox\SignedRequests\Signature;
use SoapBox\SignedRequests\Requests\Signed;
use SoapBox\SignedRequests\Requests\Payload;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Configurations;
use SoapBox\SignedRequests\Exceptions\ExpiredRequestException;
use SoapBox\SignedRequests\Exceptions\InvalidConfigurationException;
use SoapBox\SignedRequests\Exceptions\InvalidSignatureException;
use SoapBox\SignedRequests\Middlewares\Laravel\VerifySignature;

class VerifySignatureTest extends TestCase
{
    /**
     * An instance of the verify signature middleware we can use for testing.
     *
     * @var \SoapBox\SignedRequests\Middlewares\Laravel\VerifySignature
     */
    protected $middleware;

    /**
     * A mock of the configurations repository we can add expectations to.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $configurations;

    /**
     * A mock of the cache repository we can add expectations to.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * @before
     */
    public function setup_the_middleware()
    {
        $this->cache = new \Illuminate\Cache\Repository(new ArrayStore());
        $this->configurations = Mockery::mock(Configurations::class);
        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.cache-prefix')
            ->andReturn('prefix');
        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.tolerance')
            ->andReturn(60);
        $this->configurations->shouldReceive('get')
            ->with('signed-requests')
            ->andReturn([
                'default' => [],
                'custom' => []
            ]);
        $this->middleware = new VerifySignature($this->configurations, $this->cache);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(VerifySignature::class, $this->middleware);
    }

    /**
     * @test
     */
    public function it_throws_an_invalid_signature_exception_if_the_request_is_not_valid()
    {
        $this->expectException(InvalidSignatureException::class);

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('HTTP_SIGNATURE');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('HTTP_ALGORITHM');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(true);

        $request = new Request();

        $this->middleware->handle($request, function () {
        });
    }

    /**
     * @test
     */
    public function it_should_call_our_callback_if_the_request_is_valid()
    {
        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(true);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
            // This should be called.
            $this->assertTrue(true);
        });
    }

    /**
     * @test
     */
    public function it_should_set_the_key_when_one_is_passed()
    {
        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.custom.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.custom.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.custom.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.custom.request-replay.allow')
            ->andReturn(true);

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.custom.cache-prefix')
            ->andReturn('prefix');
        $this->configurations->shouldReceive('get')
            ->with('signed-requests.custom.request-replay.tolerance')
            ->andReturn(60);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $called = false;
        $this->middleware->handle($request, function () use (&$called) {
            $called = true;
        }, 'custom');
        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_it_cannot_find_the_key()
    {
        $this->expectException(InvalidConfigurationException::class);

        $id = (string) Uuid::uuid4();

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
            // This should be called.
            $this->assertTrue(true);
        }, 'nope');
    }

    /**
     * @test
     */
    public function it_throws_an_expired_request_exception_if_the_timestamp_on_the_request_is_outside_of_the_tolerance_allowed_for_requests()
    {
        $this->expectException(ExpiredRequestException::class);

        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(false);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::now()->addSeconds(100),
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
        });
    }

    /**
     * @test
     */
    public function it_should_call_our_callback_if_the_id_has_not_previously_been_seen()
    {
        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(false);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::now()->addSeconds(10),
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
            // This should be called.
            $this->assertTrue(true);
        });
    }

    /**
     * @test
     */
    public function it_should_throw_an_expired_request_exception_if_the_request_id_has_previously_been_seen()
    {
        $this->expectException(ExpiredRequestException::class);

        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(false);

        $key = sprintf('prefix.%s', $id);

        $this->cache->put($key, $key, 10);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::now()->addSeconds(10),
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
        });
    }

    /**
     * @test
     */
    public function it_should_throw_an_expired_request_exception_if_the_same_request_is_played_multiple_times()
    {
        $this->expectException(ExpiredRequestException::class);

        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(false);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::now()->addSeconds(10),
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
            // This should be called.
            $this->assertTrue(true);
        });

        $this->middleware->handle($request, function () {
        });
    }

    /**
     * @test
     */
    public function it_throws_an_expired_request_exception_if_the_timestamp_on_the_request_does_not_have_the_correct_format()
    {
        $this->expectException(ExpiredRequestException::class);

        $id = (string) Uuid::uuid4();

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.signature')
            ->andReturn('signature');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.headers.algorithm')
            ->andReturn('algorithm');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.key')
            ->andReturn('key');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.default.request-replay.allow')
            ->andReturn(false);

        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::now()->addSeconds(10)->format('Y-m-d'),
            'HTTP_ALGORITHM' => 'sha256'
        ];

        $request = new Request($query, $request, $attributes, $cookies, $files, $server, 'a');
        $request->headers->set('signature', (string) new Signature(new Payload($request), 'sha256', 'key'));

        $this->middleware->handle($request, function () {
        });
    }
}
