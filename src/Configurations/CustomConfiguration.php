<?php

namespace SoapBox\SignedRequests\Configurations;

class CustomConfiguration implements Configuration
{
    /**
     * The algorithm header to return.
     *
     * @var string
     */
    protected $algorithmHeader;

    /**
     * The signature header to return.
     *
     * @var string
     */
    protected $signatureHeader;

    /**
     * The signing algorithm to return.
     *
     * @var string
     */
    protected $signingAlgorithm;

    /**
     * The signing key to return.
     *
     * @var string
     */
    protected $signingKey;

    /**
     * Sets up our custom configuration with various properties.
     *
     * @param string $algorithmHeader
     *        The algorithm header to use.
     * @param string $signatureHeader
     *        The signature header to use.
     * @param string $signingAlgorithm
     *        The signing algorithm to use.
     * @param string $signingKey
     *        The signing key to use.
     */
    public function __construct(
        string $algorithmHeader,
        string $signatureHeader,
        string $signingAlgorithm,
        string $signingKey
    ) {
        $this->algorithmHeader = $algorithmHeader;
        $this->signatureHeader = $signatureHeader;
        $this->signingAlgorithm = $signingAlgorithm;
        $this->signingKey = $signingKey;
    }

    /**
     * Returns the name of the header that will contain the algorithm used to
     * sign the request.
     *
     * @return string
     */
    public function getAlgorithmHeader(): string
    {
        return $this->algorithmHeader;
    }

    /**
     * Returns the name of the header that will contain the generated signature.
     *
     * @return string
     */
    public function getSignatureHeader(): string
    {
        return $this->signatureHeader;
    }

    /**
     * Returns the algorithm to use to generate the signature.
     *
     * @return string
     */
    public function getSigningAlgorithm(): string
    {
        return $this->signingAlgorithm;
    }

    /**
     * Returns the key to sign the request with.
     *
     * @return string
     */
    public function getSigningKey(): string
    {
        return $this->signingKey;
    }
}
