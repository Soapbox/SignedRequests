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
     * Generates a payload with the provided properties
     *
     * @param string $identifier
     * @param string $method
     * @param string $timestamp
     * @param string $uri
     * @param string $content
     *
     * @return string
     */
    private function generate(
        string $identifier,
        string $method,
        string $timestamp,
        string $uri,
        string $content
    ) : string {
        $payload = [
            'id' => $identifier,
            'method' => strtoupper($method),
            'timestamp' => $timestamp,
            'uri' => rtrim($uri, '/')
        ];

        if (is_null(json_decode($content))) {
            $payload = array_merge($payload, [
                'content' => $content
            ]);
        } else {
            $payload = array_merge($payload, [
                'content' => json_encode(
                    json_decode($content),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            ]);
        }

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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

        return $this->generate(
            (string) $id,
            (string) $this->request->getMethod(),
            (string) $timestamp,
            (string) $this->request->getUri(),
            (string) $this->request->getBody()
        );
    }

    /**
     * Returns the payload from an illuminate request.
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

        return $this->generate(
            (string) $id,
            (string) $this->request->getMethod(),
            (string) $timestamp,
            (string) $this->request->fullUrl(),
            (string) $this->request->getContent()
        );
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
