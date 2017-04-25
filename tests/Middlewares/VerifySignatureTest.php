<?php

namespace Tests\Middlewares;

use Mockery;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Contracts\Config\Repository;
use SoapBox\SignedRequests\Requests\Signed;
use SoapBox\SignedRequests\Middlewares\VerifySignature;

class VerifySignatureTest extends TestCase
{
    /**
     * An instance of the verify signature middleware we can use for testing.
     *
     * @var \SoapBox\SignedRequests\Middlewares\VerifySignature
     */
    protected $middleware;

    /**
     * A mock of the configurations repository we can add expectations to.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $configurations;

    /**
     * @before
     */
    public function setup_the_middleware()
    {
        $this->configurations = Mockery::mock(Repository::class);
        $this->middleware = new VerifySignature($this->configurations);
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
     * @expectedException \SoapBox\SignedRequests\Exceptions\InvalidSignatureException
     */
    public function it_throws_an_invalid_signature_exception_if_the_request_is_not_valid()
    {
        $this->configurations->shouldReceive('get')
            ->with('signed-requests.headers.signature')
            ->andReturn('HTTP_SIGNATURE');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.headers.algorithm')
            ->andReturn('HTTP_ALGORITHM');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.key')
            ->andReturn('key');

        $request = new class(new Request()) extends Signed {
            public function isValid(string $key) : bool
            {
                return false;
            }
        };

        $this->middleware->handle($request, function () { });
    }

    /**
     * @test
     */
    public function it_should_call_our_callback_if_the_request_is_valid()
    {
        $this->configurations->shouldReceive('get')
            ->with('signed-requests.headers.signature')
            ->andReturn('HTTP_SIGNATURE');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.headers.algorithm')
            ->andReturn('HTTP_ALGORITHM');

        $this->configurations->shouldReceive('get')
            ->with('signed-requests.key')
            ->andReturn('key');

        $request = new class(new Request()) extends Signed {
            public function isValid(string $key) : bool
            {
                return true;
            }
        };

        $this->middleware->handle($request, function () {
            // This should be called.
            $this->assertTrue(true);
        });
    }
}
