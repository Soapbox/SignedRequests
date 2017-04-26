<?php

namespace SoapBox\SignedRequests\Requests;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request as IlluminateRequest;

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
     * @param \GuzzleHttp\Psr7\Request $request
     *        An instance of the guzzle request to extract a payload from.
     *
     * @return string
     *         The payload.
     */
    protected function generateFromGuzzleRequest(GuzzleRequest $request) : string
    {
        $id = isset($this->request->getHeader('X-SIGNED-ID')[0]) ?
            $this->request->getHeader('X-SIGNED-ID')[0] : '';

        return json_encode([
            'id' => (string) $id,
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->getUri(),
            'content' => $this->request->getBody()
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

        return json_encode([
            'id' => (string) $id,
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->fullUrl(),
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
        if ($this->request instanceof GuzzleRequest) {
            return $this->generateFromGuzzleRequest($this->request);
        }

        if ($this->request instanceof IlluminateRequest) {
            return $this->generateFromIlluminateRequest($this->request);
        }

        return '';
    }
}
