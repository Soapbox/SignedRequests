<?php

namespace Tests\Configurations;

use Mockery;
use Tests\TestCase;
use Illuminate\Contracts\Config\Repository;
use SoapBox\SignedRequests\Configurations\RepositoryConfiguration;

class RepositoryConfigurationTest extends TestCase
{
    protected $repository;
    protected $configuration;

    /**
     * @before
     */
    public function setup_repository()
    {
        $this->repository = Mockery::mock(Repository::class);
        $this->configuration = new RepositoryConfiguration($this->repository);
    }

    /**
     * @test
     */
    public function get_algorithm_header_returns_the_algorithm_header_value_from_the_configuration_repository()
    {
        $this->repository->shouldReceive('get')
            ->with('signed-requests.headers.algorithm')
            ->andReturn('algorithm');

        $this->assertSame('algorithm', $this->configuration->getAlgorithmHeader());
    }

    /**
     * @test
     */
    public function get_signature_header_returns_the_signature_header_value_from_the_configuration_repository()
    {
        $this->repository->shouldReceive('get')
            ->with('signed-requests.headers.signature')
            ->andReturn('signature');

        $this->assertSame('signature', $this->configuration->getSignatureHeader());
    }

    /**
     * @test
     */
    public function get_signing_algorithm_returns_the_signing_algorithm_to_use_from_the_configuration_repository()
    {
        $this->repository->shouldReceive('get')
            ->with('signed-requests.algorithm')
            ->andReturn('sha256');

        $this->assertSame('sha256', $this->configuration->getSigningAlgorithm());
    }

    /**
     * @test
     */
    public function get_signing_key_returns_the_key_used_for_signing_the_request_from_the_configuration_repository()
    {
        $this->repository->shouldReceive('get')
            ->with('signed-requests.key')
            ->andReturn('key');

        $this->assertSame('key', $this->configuration->getSigningKey());
    }
}
