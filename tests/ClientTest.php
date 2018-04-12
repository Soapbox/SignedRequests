<?php

namespace Tests;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use JSHayes\FakeRequests\MockHandler;
use Ramsey\Uuid\UuidFactoryInterface;
use JSHayes\FakeRequests\ClientFactory;
use SoapBox\SignedRequests\Configurations\CustomConfiguration;
use SoapBox\SignedRequests\Middlewares\Guzzle\GenerateSignature;

class ClientTest extends TestCase
{
    private function expectUuid4(string $uuid): void
    {
        Uuid::setFactory(new class($uuid) implements UuidFactoryInterface {
            public function __construct(string $uuid)
            {
                $this->uuid = $uuid;
            }

            public function uuid1($node = null, $clockSeq = null)
            {
                return null;
            }

            public function uuid3($ns, $name)
            {
                return null;
            }

            public function uuid4()
            {
                return $this->uuid;
            }

            public function uuid5($ns, $name)
            {
                return null;
            }

            public function fromBytes($bytes)
            {
                return null;
            }

            public function fromString($uuid)
            {
                return null;
            }

            public function fromInteger($integer)
            {
                return null;
            }
        });
    }

    protected function setUp()
    {
        parent::setUp();

        $factory = new ClientFactory();
        $factory->setHandler($this->handler = new MockHandler());

        $algorithmHeader = 'Algorithm';
        $signatureHeader = 'Signature';
        $signingAlgorithm = 'sha256';
        $signingKey = 'key';

        $config = new CustomConfiguration(
            $algorithmHeader,
            $signatureHeader,
            $signingAlgorithm,
            $signingKey
        );

        $middleware = new GenerateSignature($config);

        $this->client = $factory->make();
        $this->client->getConfig('handler')->push($middleware);
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_simple_json_payload()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost';

        $this->handler->expects('POST', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    'b9f912a4fc4b2952a48380579d3e4a1c55c0537ce583b3da7cc9f6c67fe4caa7',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post($uri, ['json' => ['test' => 'test']]);
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_simple_json_payload_containing_ã()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost';

        $this->handler->expects('POST', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    'd35d92484222fce7e5c194381e5f53342caae6fa626cd61e3431bddc549b34e1',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post($uri, ['json' => ['test' => 'ã']]);
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_simple_json_payload_containing_好()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost';

        $this->handler->expects('POST', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    '65ff94dce4894eb306a76ff0d397ec264b1c4980b57afbc3dd9526af242d239b',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post($uri, ['json' => ['test' => '好']]);
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_simple_json_payload_containing_a_uri()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost';

        $this->handler->expects('POST', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    'ebd68bfe7ed51c050fb92db098946cd21b7b23be6f682360a5e893840a1dc52f',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post($uri, ['json' => ['test' => $uri]]);
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_complex_json_payload()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost/poop';

        $this->handler->expects('POST', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    '0c3f0c81ba1fa3df9d3e0a1d72c4d491125153c0dea8355b6d48fe7ef1a4dacc',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post(
            $uri,
            [
                'json' => [
                    'users' => [
                        ['id' => 1, 'name' => 'Chris Hayes', 'email' => 'hayes@soapboxhq.com'],
                        ['id' => 2, 'name' => 'Jaspaul Bola', 'email' => 'jaspaul@soapboxhq.com'],
                        ['id' => 3, 'name' => 'Mr Penã 💩', 'email' => 'Mr-Penã@soapboxhq.com']
                    ]
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_complex_json_payload_after_stripping_the_trailing_slash()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost/poop/';

        $this->handler->expects('POST', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    '0c3f0c81ba1fa3df9d3e0a1d72c4d491125153c0dea8355b6d48fe7ef1a4dacc',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post(
            $uri,
            [
                'json' => [
                    'users' => [
                        ['id' => 1, 'name' => 'Chris Hayes', 'email' => 'hayes@soapboxhq.com'],
                        ['id' => 2, 'name' => 'Jaspaul Bola', 'email' => 'jaspaul@soapboxhq.com'],
                        ['id' => 3, 'name' => 'Mr Penã 💩', 'email' => 'Mr-Penã@soapboxhq.com']
                    ]
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_simple_json_get_payload()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost';

        $this->handler->expects('GET', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    '939ada016b60aa267980a73f62e6dc583b03b35a2abf0dea5b054871d6c6a306',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->get($uri, ['json' => ['payload' => 'payload']]);
    }

    /**
     * @test
     */
    public function it_generates_a_signature_with_a_simple_get_payload()
    {
        Carbon::setTestNow('2001-01-01 00:00:00');
        $this->expectUuid4('303103f5-3dca-4704-96ad-860717769ec9');

        $uri = 'https://localhost';

        $this->handler->expects('GET', $uri)
            ->inspectRequest(function ($request) use ($uri) {
                $this->assertTrue($request->hasHeader('Algorithm'));
                $this->assertTrue($request->hasHeader('Signature'));
                $this->assertSame(
                    '9feb58dfece796627b16f7865fc19ee6bfc5b231d49b12d83170d74d22bf9641',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->get($uri, ['body' => 'payload']);
    }
}
