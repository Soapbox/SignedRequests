<?php

namespace SoapBox\SignedRequests\Requests;

use GuzzleHttp\Psr7\Request;

class Payload
{
    /**
     * The request.
     *
     * @var \GuzzleHttp\Psr7\Request
     */
    private $request;

    /**
     * Set's the local request to extract a payload from.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     *        The request to extract a payload from.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns a payload with the id, method, uri, and content embedded.
     *
     * @return string
     *         A json encoded payload.
     */
    public function __toString() : string
    {
        $id = isset($this->request->getHeader('X-SIGNED-ID')[0]) ?
            $this->request->getHeader('X-SIGNED-ID')[0] : '';

        return json_encode([
            'id' => (string) $id,
            'method' => $this->request->getMethod(),
            'uri' => (string) $this->request->getUri(),
            'content' => $this->request->getBody()
        ]);
    }
}
