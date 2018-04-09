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
                    'bf0f2eb48acf86cf72a87b48393f71fb2eebbb2c11fa0d838cbb127d74a0a00e',
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
                    '10b165e59775d1a564be49046edd60137d40fcecebbdf59f41b01568ca07db63',
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
                    '8c36d7384111d27336c410ebfec38c7da2eca9ec4779216f9cb8f921a08c4572',
                    $request->getHeader('Signature')[0]
                );
            });

        $this->client->post($uri, ['json' => ['test' => $uri]]);
    }
}
