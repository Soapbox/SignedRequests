<?php

namespace Tests\Requests;

use Carbon\Carbon;
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
        $now = (string) Carbon::now();

        $method = 'GET';
        $uri = 'https://localhost';
        $id = Uuid::uuid4();

        $request = (new GuzzleRequest('GET', 'https://localhost'))
            ->withHeader('X-SIGNED-ID', $id)
            ->withHeader('X-SIGNED-TIMESTAMP', $now);

        $expected = json_encode([
            'id' => $id,
            'method' => $method,
            'timestamp' => $now,
            'uri' => $uri,
            'content' => $request->getBody()->getContents()
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_translates_a_guzzle_request_with_content_to_a_json_encoded_string()
    {
        $now = (string) Carbon::now();

        $method = 'GET';
        $uri = 'https://localhost';
        $id = Uuid::uuid4();

        $request = (new GuzzleRequest('GET', 'https://localhost', [], 'content'))
            ->withHeader('X-SIGNED-ID', $id)
            ->withHeader('X-SIGNED-TIMESTAMP', $now);

        $expected = json_encode([
            'id' => $id,
            'method' => $method,
            'timestamp' => $now,
            'uri' => $uri,
            'content' => 'content'
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_translates_an_illuminate_request_to_a_json_encoded_string()
    {
        $now = (string) Carbon::now();
        $id = (string) Uuid::uuid4();

        $uri = 'https://localhost';
        $method = 'GET';
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => $now
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
            'timestamp' => $now,
            'uri' => $uri,
            'content' => $request->getContent()
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_upper_cases_the_illuminate_request_method()
    {
        $now = (string)Carbon::now();
        $id = (string)Uuid::uuid4();

        $uri = 'https://localhost';
        $method = 'get';
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => $now
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
            'method' => 'GET',
            'timestamp' => $now,
            'uri' => $uri,
            'content' => $request->getContent()
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string)new Payload($request));
    }


    /**
     * @test
     */
    public function it_upper_cases_the_guzzle_request_method()
    {
        $now = (string)Carbon::now();

        $uri = 'https://localhost';
        $id = Uuid::uuid4();

        $request = (new GuzzleRequest('get', 'https://localhost', [], 'content'))
            ->withHeader('X-SIGNED-ID', $id)
            ->withHeader('X-SIGNED-TIMESTAMP', $now);

        $expected = json_encode([
            'id' => $id,
            'method' => 'GET',
            'timestamp' => $now,
            'uri' => $uri,
            'content' => 'content'
        ], JSON_UNESCAPED_SLASHES);

        $this->assertEquals($expected, (string)new Payload($request));
    }

    /**
     * @test
     */
    public function it_translates_non_requests_to_an_empty_string()
    {
        $this->assertEquals('', (string) new Payload(null));
    }

    /**
     * @test
     */
    public function it_stringifies_a_simple_payload_to_a_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], 'content'))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"content"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_payload_with_an_embedded_url_to_a_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], 'https://google.com'))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"https://google.com"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_payload_with_the_ã_character_to_use_the_escaped_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], 'ã'))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"ã"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_payload_with_the_好_character_to_use_the_escaped_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], '好'))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"好"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_json_payload_to_a_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], json_encode(['test' => 'test'])))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"{\"test\":\"test\"}"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_json_payload_with_the_ã_character_to_a_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], json_encode(['ã' => 'ã'], JSON_UNESCAPED_UNICODE)))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"{\"ã\":\"ã\"}"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_json_payload_with_the_好_character_to_a_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], json_encode(['好' => '好'], JSON_UNESCAPED_UNICODE)))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"{\"好\":\"好\"}"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }

    /**
     * @test
     */
    public function it_stringifies_a_json_payload_with_a_url_to_a_string()
    {
        $request = (new GuzzleRequest('GET', 'https://localhost', [], json_encode(['url' => 'https://google.com'], JSON_UNESCAPED_UNICODE)))
            ->withHeader('X-SIGNED-ID', '303103f5-3dca-4704-96ad-860717769ec9')
            ->withHeader('X-SIGNED-TIMESTAMP', '2018-04-06 20:34:47');

        $expected = '{"id":"303103f5-3dca-4704-96ad-860717769ec9","method":"GET","timestamp":"2018-04-06 20:34:47","uri":"https://localhost","content":"{\"url\":\"https:\\\\/\\\\/google.com\"}"}';

        $this->assertEquals($expected, (string) new Payload($request));
    }
}
