<?php

namespace Tests\Requests;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Psr7\Request;
use SoapBox\SignedRequests\Requests\Payload;

class PayloadTest extends TestCase
{
    /**
     * @test
     */
    public function it_translates_a_request_to_json_encoded_string()
    {
        $method = 'GET';
        $uri = 'https://localhost';
        $id = Uuid::uuid4();

        $request = (new Request('GET', 'https://localhost'))
            ->withHeader('X-SIGNED-ID', $id);

        $expected = json_encode([
            'id' => $id,
            'method' => $method,
            'uri' => $uri,
            'content' => $request->getBody()
        ]);

        $this->assertEquals($expected, (string) new Payload($request));
    }
}
