<?php

namespace Tests\Requests;

use Mockery;
use Tests\TestCase;
use GuzzleHttp\Psr7\Request;
use SoapBox\SignedRequests\Signature;
use SoapBox\SignedRequests\Requests\Payload;
use SoapBox\SignedRequests\Requests\Generator;
use SoapBox\SignedRequests\Configurations\Configuration;

class GeneratorTest extends TestCase
{
    private $configuration;
    private $generator;

    /**
     * @before
     */
    public function setup_a_local_generator()
    {
        $this->configuration = Mockery::mock(Configuration::class);

        $this->configuration
            ->shouldReceive('getAlgorithmHeader')
            ->andReturn('X-ALGORITHM');
        $this->configuration
            ->shouldReceive('getSignatureHeader')
            ->andReturn('X-SIGNATURE');
        $this->configuration
            ->shouldReceive('getSigningAlgorithm')
            ->andReturn('sha256');
        $this->configuration
            ->shouldReceive('getSigningKey')
            ->andReturn('key');

        $this->generator = new Generator($this->configuration);
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
