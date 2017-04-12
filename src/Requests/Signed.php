<?php

namespace SoapBox\SignedRequests\Requests;

use Illuminate\Http\Request;
use SoapBox\SignedRequests\Signature;

class Signed extends Request
{
    /**
     * The header that holds the signature.
     *
     * @var string
     */
    protected $signatureHeader;

    /**
     * The header that holds the algorithm.
     *
     * @var string
     */
    protected $algorithmHeader;

    /**
     * Sets the local header key for locating the signature to the provided key.
     *
     * @param string $header
     *        The header key where the signature is located.
     *
     * @return \SoapBox\SignedRequests\Requests\Signed
     *         The updated instance to enable fluent access.
     */
    public function setSignatureHeader(string $header) : Signed
    {
        $this->signatureHeader = $header;
        return $this;
    }

    /**
     * Sets the local header key for locating the algorithm to the provided key.
     *
     * @param string $header
     *        The header key where the algorithm is located.
     *
     * @return \SoapBox\SignedRequests\Requests\Signed
     *         The updated instance of to enable fluent access.
     */
    public function setAlgorithmHeader(string $header) : Signed
    {
        $this->algorithmHeader = $header;
        return $this;
    }

    /**
     * Returns the algorithm from the request.
     *
     * @return string
     *         The algorithm used to sign the request.
     */
    protected function getAlgorithm() : string
    {
        return $this->header($this->algorithmHeader);
    }

    /**
     * Returns the signature from the request.
     *
     * @return string
     *         The signature of the request.
     */
    protected function getSignature() : string
    {
        return $this->header($this->signatureHeader);
    }

    /**
     * Returns the request body content, and handles unescaping slashes for
     * json content.
     *
     * @throws \LogicException
     *
     * @param bool $asResource
     *        If true, a resource will be returned.
     *
     * @return mixed
     *         The request body content or a resource to read the body stream.
     */
    public function getContent($asResource = false)
    {
        $content = parent::getContent($asResource);

        json_decode($content);

        if (json_last_error() == JSON_ERROR_NONE) {
            return json_encode(json_decode($content), JSON_UNESCAPED_SLASHES);
        }

        return $content;
    }

    /**
     * Determines if the signed request is valid.
     *
     * @param string $key
     *        The key to validate the signature with.
     *
     * @return bool
     *         true if the signature matches the computed signature, false
     *         otherwise.
     */
    public function isValid(string $key) : bool
    {
        if (!$this->headers->has($this->algorithmHeader)) {
            return false;
        }

        if (!$this->headers->has($this->signatureHeader)) {
            return false;
        }

        $signature = new Signature($this->getContent(), $this->getAlgorithm(), $key);

        return $signature->equals($this->getSignature());
    }
}
