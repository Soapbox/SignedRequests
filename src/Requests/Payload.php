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

        $payload = [
            'id' => (string) $id,
            'method' => strtoupper($this->request->getMethod()),
            'timestamp' => $timestamp,
            'uri' => rtrim((string) $this->request->getUri(), '/')
        ];

        if (is_null(json_decode((string) $this->request->getBody()))) {
            $payload = array_merge($payload, [
                'content' => (string) $this->request->getBody()
            ]);
        } else {
            $payload = array_merge($payload, [
                'content' => json_encode(
                    json_decode((string) $this->request->getBody()),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            ]);
        }

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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

        $payload = [
            'id' => (string) $id,
            'method' => strtoupper($this->request->getMethod()),
            'timestamp' => $timestamp,
            'uri' => rtrim((string) $this->request->fullUrl(), '/'),
        ];

        if (is_null(json_decode((string) $this->request->getContent()))) {
            $payload = array_merge($payload, [
                'content' => (string) $this->request->getContent()
            ]);
        } else {
            $payload = array_merge($payload, [
                'content' => json_encode(
                    json_decode((string)$this->request->getContent()),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            ]);
        }

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
