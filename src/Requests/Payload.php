<?php

namespace SoapBox\SignedRequests\Requests;

use Illuminate\Http\Request as IlluminateRequest;
use Psr\Http\Message\RequestInterface as Psr7Request;

class Payload
{
    /**
     * A request object. Currently both \GuzzleHttp\Psr7\Request and
     * \Illuminate\Http\Request are supported.
     *
     * @var mixed
     */
    private $request;

    /**
     * Set's the local request to extract a payload from.
     *
     * @param mixed $request
     *        The request to extract a payload from. Currently both
     *        \GuzzleHttp\Psr7\Request and \Illuminate\Http\Request are
     *        supported.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Returns the payload from a guzzle request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *        An instance of the guzzle request to extract a payload from.
     *
     * @return string
     *         The payload.
     */
    protected function generateFromPsr7Request(Psr7Request $request) : string
    {
        $id = isset($this->request->getHeader('X-SIGNED-ID')[0]) ?
            $this->request->getHeader('X-SIGNED-ID')[0] : '';
        $timestamp = isset($this->request->getHeader('X-SIGNED-TIMESTAMP')[0]) ?
            $this->request->getHeader('X-SIGNED-TIMESTAMP')[0] : '';

        return json_encode([
            'id' => (string) $id,
            'method' => $this->request->getMethod(),
            'timestamp' => $timestamp,
            'uri' => (string) $this->request->getUri(),
            'content' => $this->request->getBody()->getContents()
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Retruns the payload from an illuminate request.
     *
     * @param \Illuminate\Http\Request $request
     *        An instance of the illuminate request to extract the payload from.
     *
     * @return string
     *         The payload.
     */
    protected function generateFromIlluminateRequest(IlluminateRequest $request) : string
    {
        $id = $this->request->headers->get('X-SIGNED-ID', '');
        $timestamp = $this->request->headers->get('X-SIGNED-TIMESTAMP', '');
        $query = !empty($this->request->query()) ? '?' . http_build_query($this->request->query()) : '';

        return json_encode([
            'id' => (string) $id,
            'method' => $this->request->getMethod(),
            'timestamp' => $timestamp,
            'uri' => (string) $this->request->url() . $query,
            'content' => $this->request->getContent()
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Returns a payload with the id, method, uri, and content embedded.
     *
     * @return string
     *         A json encoded payload.
     */
    public function __toString() : string
    {
        if ($this->request instanceof Psr7Request) {
            return $this->generateFromPsr7Request($this->request);
        }

        if ($this->request instanceof IlluminateRequest) {
            return $this->generateFromIlluminateRequest($this->request);
        }

        return '';
    }
}
