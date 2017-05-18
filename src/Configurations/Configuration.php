<?php

namespace SoapBox\SignedRequests\Configurations;

interface Configuration
{
    /**
     * Returns the name of the header that will contain the algorithm used to
     * sign the request.
     *
     * @return string
     */
    public function getAlgorithmHeader(): string;

    /**
     * Returns the name of the header that will contain the generated signature.
     *
     * @return string
     */
    public function getSignatureHeader(): string;

    /**
     * Returns the algorithm to use to generate the signature.
     *
     * @return string
     */
    public function getSigningAlgorithm(): string;

    /**
     * Returns the key to sign the request with.
     *
     * @return string
     */
    public function getSigningKey(): string;
}
