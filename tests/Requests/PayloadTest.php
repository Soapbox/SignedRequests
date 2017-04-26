<?php

namespace Tests\Requests;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use SoapBox\SignedRequests\Requests\Payload;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request as IlluminateRequest;

class PayloadTest extends TestCase
{
    /**
     * @test
     */
    public function it_translates_a_guzzle_request_to_a_json_encoded_string()
    {
        $method = 'GET';
        $uri = 'https://localhost';
        $id = Uuid::uuid4();

        $request = (new GuzzleRequest('GET', 'https://localhost'))
            ->withHeader('X-SIGNED-ID', $id);

        $expected = json_encode([
            'id' => $id,
            'method' => $method,
            'uri' => $uri,
            'content' => $request->getBody()
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_translates_an_illuminate_request_to_a_json_encoded_string()
    {
        $id = (string) Uuid::uuid4();

        $uri = 'https://localhost';
        $method = 'GET';
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id
        ];
        $content = null;

        $request = IlluminateRequest::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );

        $expected = json_encode([
            'id' => $id,
            'method' => $method,
            'uri' => $uri,
            'content' => $request->getContent()
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_translates_non_requests_to_an_empty_string()
    {
        $this->assertEquals('', (string) new Payload(null));
    }
}
