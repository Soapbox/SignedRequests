<?php

namespace Tests\Midlewares\Guzzle;

use Mockery;
use Tests\TestCase;
use GuzzleHttp\Psr7\Request;
use Illuminate\Config\Repository;
use Psr\Http\Message\RequestInterface;
use SoapBox\SignedRequests\Requests\Generator;
use SoapBox\SignedRequests\Configurations\Configuration;
use SoapBox\SignedRequests\Middlewares\Guzzle\GenerateSignature;
use SoapBox\SignedRequests\Configurations\RepositoryConfiguration;

class GenerateSignatureTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $middleware = new GenerateSignature(Mockery::mock(Configuration::class));
        $this->assertInstanceOf(GenerateSignature::class, $middleware);
    }

    /**
     * @test
     */
    public function it_signs_the_request()
    {
        $configurations = Mockery::mock(Repository::class);
        $configurations->shouldReceive('get')
            ->with('signed-requests.cache-prefix')
            ->andReturn('prefix');
        $configurations->shouldReceive('get')
            ->with('signed-requests.request-replay.tolerance')
            ->andReturn(60);
        $configurations->shouldReceive('get')
            ->with('signed-requests.headers.algorithm')
            ->andReturn('Test-Algorithm');
        $configurations->shouldReceive('get')
            ->with('signed-requests.headers.signature')
            ->andReturn('Test-Signature');
        $algorithm = $configurations->shouldReceive('get')
            ->with('signed-requests.algorithm')
            ->andReturn('sha256');
        $key = $configurations->shouldReceive('get')
            ->with('signed-requests.key')
            ->andReturn('bigsecretrighthereitellyouwhat');
        $middleware = new GenerateSignature(new RepositoryConfiguration($configurations));

        $nextHandler = function (RequestInterface $request, array $options) {
            return $request;
        };

        $request = new Request('get', 'tested');

        $handler = call_user_func_array($middleware, [$nextHandler]);
        $request = $handler($request, []);

        $this->assertTrue($request->hasHeader('Test-Signature'));
        $this->assertTrue($request->hasHeader('Test-Algorithm'));
        $this->assertSame('sha256', $request->getHeader('Test-Algorithm')[0]);
    }
}
