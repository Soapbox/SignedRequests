<?php

namespace Tests\Requests;

use Mockery;
use Tests\TestCase;
use GuzzleHttp\Psr7\Request;
use SoapBox\SignedRequests\Signature;
use Illuminate\Contracts\Config\Repository;
use SoapBox\SignedRequests\Requests\Payload;
use SoapBox\SignedRequests\Requests\Generator;

class GeneratorTest extends TestCase
{
    private $repository;
    private $generator;

    /**
     * @before
     */
    public function setup_a_local_generator()
    {
        $this->repository = Mockery::mock(Repository::class);

        $this->repository
            ->shouldReceive('get')
            ->with('signed-requests.headers.algorithm')
            ->andReturn('X-ALGORITHM');
        $this->repository
            ->shouldReceive('get')
            ->with('signed-requests.headers.signature')
            ->andReturn('X-SIGNATURE');
        $this->repository
            ->shouldReceive('get')
            ->with('signed-requests.algorithm')
            ->andReturn('sha256');
        $this->repository
            ->shouldReceive('get')
            ->with('signed-requests.key')
            ->andReturn('key');

        $this->generator = new Generator($this->repository);
    }

    /**
     * @test
     */
    public function it_adds_an_id_header_to_the_request()
    {
        $request = new Request('GET', 'https://localhost');
        $this->assertNotContains('X-SIGNED-ID', array_keys($request->getHeaders()));
        $request = $this->generator->sign($request);
        $this->assertContains('X-SIGNED-ID', array_keys($request->getHeaders()));
    }

    /**
     * @test
     */
    public function it_adds_an_algorithm_header_to_the_request()
    {
        $request = new Request('GET', 'https://localhost');
        $this->assertNotContains('X-ALGORITHM', array_keys($request->getHeaders()));
        $request = $this->generator->sign($request);
        $this->assertContains('X-ALGORITHM', array_keys($request->getHeaders()));
        $this->assertSame('sha256', $request->getHeaders()['X-ALGORITHM'][0]);
    }

    /**
     * @test
     */
    public function it_adds_a_signature_header_to_the_request()
    {
        $request = new Request('GET', 'https://localhost');
        $this->assertNotContains('X-SIGNATURE', array_keys($request->getHeaders()));
        $request = $this->generator->sign($request);
        $this->assertContains('X-SIGNATURE', array_keys($request->getHeaders()));

        $signature = new Signature(new Payload($request), 'sha256', 'key');

        $this->assertSame((string) $signature, $request->getHeaders()['X-SIGNATURE'][0]);
    }
}
