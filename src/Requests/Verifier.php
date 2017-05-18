<?php

namespace SoapBox\SignedRequests\Requests;

use Carbon\Carbon;
use Illuminate\Http\Request;
use SoapBox\SignedRequests\Signature;

class Verifier
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
     * The underlying request that has the signature to validate.
     *
     * @var \Illluminate\Http\Request
     */
    protected $request;

    /**
     * Sets the local header key for locating the signature to the provided key.
     *
     * @param string $header
     *        The header key where the signature is located.
     *
     * @return \SoapBox\SignedRequests\Requests\Verifier
     *         The updated instance to enable fluent access.
     */
    public function setSignatureHeader(string $header) : Verifier
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
     * @return \SoapBox\SignedRequests\Requests\Verifier
     *         The updated instance of to enable fluent access.
     */
    public function setAlgorithmHeader(string $header) : Verifier
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
     * Used to wrap the existing request so we can verify the signature.
     *
     * @param \Illuminate\Http\Request $request
     *        The request to be verified.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Forward calls to the underlying request so we can use this object like a
     * request.
     *
     * @param string $method
     *        The method to call on the underlying request.
     * @param mixed $parameters
     *        The parameters to send to the method on the request.
     *
     * @return mixed
     *         Returns the results of the calls on the parent.
     */
    public function __call($method, $parameters)
    {
        return $this->request->$method(...$parameters);
    }

    /**
     * Forward calls to parameters to the request.
     *
     * @param string $key
     *        The name of the property we're attempting to access.
     *
     * @return mixed
     *         The value of the property on the request.
     */
    public function __get($key)
    {
        return $this->request->$key;
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
        $content = $this->request->getContent($asResource);

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

        $signature = new Signature(new Payload($this->request), $this->getAlgorithm(), $key);

        return $signature->equals($this->getSignature());
    }

    /**
     * Checks if this request was issued within tolerance seconds of now.
     *
     * @param int $tolerance
     *        The number of seconds we'll tolerate for a request delay.
     *
     * @return bool
     *         true if the request was issued within tolerance seconds, false
     *         otherwise.
     */
    public function isExpired(int $tolerance) : bool
    {
        $issuedAt =
            Carbon::parse($this->headers->get('X-SIGNED-TIMESTAMP', '1901-01-01 12:00:00'));

        return Carbon::now()->diffInSeconds($issuedAt) > $tolerance;
    }

    /**
     * Returns the X-SIGNED-ID header value.
     *
     * @return string
     *         The configured header.
     */
    public function getId() : string
    {
        return $this->headers->get('X-SIGNED-ID', '');
    }
}
