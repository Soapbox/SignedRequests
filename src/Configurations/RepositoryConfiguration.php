<?php

namespace SoapBox\SignedRequests\Configurations;

use Illuminate\Contracts\Config\Repository;

class RepositoryConfiguration implements Configuration
{
    /**
     * An instance of the configuration repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $repository;

    /**
     * Sets up our configuration with an instance of the laravel configuration
     * repository.
     *
     * @param \Illuminate\Contracts\Config\Repository $repository
     *        A configuration repository.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns the name of the header that will contain the algorithm used to
     * sign the request.
     *
     * @return string
     */
    public function getAlgorithmHeader(): string
    {
        return $this->repository->get('signed-requests.headers.algorithm');
    }

    /**
     * Returns the name of the header that will contain the generated signature.
     *
     * @return string
     */
    public function getSignatureHeader(): string
    {
        return $this->repository->get('signed-requests.headers.signature');
    }

    /**
     * Returns the algorithm to use to generate the signature.
     *
     * @return string
     */
    public function getSigningAlgorithm(): string
    {
        return $this->repository->get('signed-requests.algorithm');
    }

    /**
     * Returns the key to sign the request with.
     *
     * @return string
     */
    public function getSigningKey(): string
    {
        return $this->repository->get('signed-requests.key');
    }
}
