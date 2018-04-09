<?php

namespace Tests\Requests;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Http\Request;
use SoapBox\SignedRequests\Signature;
use SoapBox\SignedRequests\Requests\Payload;
use SoapBox\SignedRequests\Requests\Verifier;

class VerifierTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $request = new Verifier(new Request());
        $this->assertInstanceOf(Verifier::class, $request);
    }

    /**
     * @test
     */
    public function the_signature_header_key_can_be_set()
    {
        $request = new class(new Request()) extends Verifier {
            public function getSignatureHeader()
            {
                return $this->signatureHeader;
            }
        };

        $this->assertEquals(
            'test.header',
            $request->setSignatureHeader('test.header')->getSignatureHeader()
        );

        $this->assertNotEquals('', $request->getSignatureHeader());
    }

    /**
     * @test
     */
    public function the_algorithm_header_key_can_be_set()
    {
        $request = new class(new Request()) extends Verifier {
            public function getAlgorithmHeader()
            {
                return $this->algorithmHeader;
            }
        };

        $this->assertEquals(
            'test.header',
            $request->setAlgorithmHeader('test.header')->getAlgorithmHeader()
        );

        $this->assertNotEquals('', $request->getAlgorithmHeader());
    }

    /**
     * A test helper to generate a Signed request.
     *
     * @param  array $headers
     *         The request headers we'd like to include.
     * @param  string $content
     *         The content of the request.
     *
     * @return \SoapBox\SignedRequests\Requests\Verifier
     *         A configured signed request.
     */
    protected function makeSignedRequest($id, array $headers = [], string $content = null) : Verifier
    {
        $uri = 'https://localhost';
        $method = 'GET';
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = array_merge([
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::parse('2017-10-10 12:00:00')->format('Y-m-d H:i:s')
        ], $headers);

        $request = Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );

        return new Verifier($request);
    }

    /**
     * A test helper to generate a Signed request.
     *
     * @param  array $headers
     *         The request headers we'd like to include.
     * @param  string $content
     *         The content of the request.
     *
     * @return \SoapBox\SignedRequests\Requests\Verifier
     *         A configured signed request.
     */
    protected function makeSignedPostRequest($id, array $headers = [], string $content = null, string $uri = 'https://localhost') : Verifier
    {
        $method = 'POST';
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = array_merge([
            'HTTP_X-SIGNED-ID' => $id,
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::parse('2017-10-10 12:00:00')->format('Y-m-d H:i:s')
        ], $headers);

        $request = Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );

        return new Verifier($request);
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_signature_header_key_is_not_set()
    {
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '9c44bdc4bac17149f3aba3778d74a9e217a41446b723efc5c3c903c557ba466e'
        ], "payload");
        $request->setAlgorithmHeader('ALGORITHM');
        $this->assertFalse($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_algorithm_header_key_is_not_set()
    {
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '9c44bdc4bac17149f3aba3778d74a9e217a41446b723efc5c3c903c557ba466e'
        ], "payload");
        $request->setSignatureHeader('SIGNATURE');
        $this->assertFalse($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_signature_header_is_not_set_on_the_request()
    {
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256'
        ], "payload");
        $request->setAlgorithmHeader('ALGORITHM')
            ->setSignatureHeader('SIGNATURE');
        $this->assertFalse($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_algorithm_header_is_not_set_on_the_request()
    {
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_SIGNATURE' => '9c44bdc4bac17149f3aba3778d74a9e217a41446b723efc5c3c903c557ba466e'
        ], "payload");
        $request->setAlgorithmHeader('ALGORITHM')
            ->setSignatureHeader('SIGNATURE');
        $this->assertFalse($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_valid_if_the_signature_matches_the_signature_generated_with_the_request()
    {
        $id = "303103f5-3dca-4704-96ad-860717769ec9";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
            'HTTP_SIGNATURE' => '9feb58dfece796627b16f7865fc19ee6bfc5b231d49b12d83170d74d22bf9641'
        ], "payload");
        $request->setAlgorithmHeader('ALGORITHM')
            ->setSignatureHeader('SIGNATURE');
        $this->assertTrue($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_valid_if_the_signature_matches_the_signature_generated_with_the_request_with_json_content()
    {
        $id = "303103f5-3dca-4704-96ad-860717769ec9";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
            'HTTP_SIGNATURE' => '939ada016b60aa267980a73f62e6dc583b03b35a2abf0dea5b054871d6c6a306'
        ], "{\"payload\": \"payload\"}");
        $request->setAlgorithmHeader('ALGORITHM')
            ->setSignatureHeader('SIGNATURE');
        $this->assertTrue($request->isValid("key"));
    }

    /**
     * @test
     */
    public function is_expired_returns_true_if_no_timestamp_is_provided_on_the_request()
    {
        $tolerance = 10;

        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [], "payload");

        $this->assertTrue($request->isExpired($tolerance));
    }

    /**
     * @test
     */
    public function is_expired_returns_false_if_the_timestamp_is_within_the_tolerance_window()
    {
        $tolerance = 100;

        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_X-SIGNED-TIMESTAMP' => (string) Carbon::now()
        ], "payload");

        $this->assertFalse($request->isExpired($tolerance));
    }

    /**
     * @test
     */
    public function is_expired_returns_true_if_the_timestamp_is_outside_the_tolerance_window()
    {
        $tolerance = 1000;

        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_X-SIGNED-TIMESTAMP' => (string) Carbon::now()->subSeconds($tolerance + 1)
        ], "payload");

        $this->assertTrue($request->isExpired($tolerance));
    }

    /**
     * @test
     */
    public function is_expired_returns_false_if_the_timestamp_is_in_the_future_but_within_the_tolerance()
    {
        $tolerance = 10000;

        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_X-SIGNED-TIMESTAMP' => (string) Carbon::now()->addSeconds($tolerance - 1)
        ], "payload");

        $this->assertFalse($request->isExpired($tolerance));
    }

    /**
     * @test
     */
    public function is_expired_returns_if_the_timestamp_is_in_the_future_outside_of_the_tolerance()
    {
        $tolerance = 100000;

        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_X-SIGNED-TIMESTAMP' => (string) Carbon::now()->addSeconds($tolerance + 1)
        ], "payload");

        $this->assertTrue($request->isExpired($tolerance));
    }

    /**
     * @test
     */
    public function get_id_should_return_the_x_signed_id_value()
    {
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [], '');

        $this->assertSame($id, $request->getId());
    }

    /**
     * @test
     */
    public function get_content_returns_the_raw_content_if_it_is_not_valid_json()
    {
        $request = new class() extends Request {
            public function getContent($asResource = false)
            {
                return '"url":"http:\\/\\/google.com"';
            }
        };

        $verifier = new Verifier($request);

        $this->assertSame('"url":"http:\\/\\/google.com"', $verifier->getContent());
    }

    /**
     * @test
     */
    public function get_content_returns_the_content_from_the_request_without_escaping_the_slashes_if_the_content_is_valid_json()
    {
        $request = new class() extends Request {
            public function getContent($asResource = false)
            {
                return '{"url":"http:\\/\\/google.com"}';
            }
        };

        $verifier = new Verifier($request);

        $this->assertSame('{"url":"http://google.com"}', $verifier->getContent());
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_correctly_when_encoded_without_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => 'b9f912a4fc4b2952a48380579d3e4a1c55c0537ce583b3da7cc9f6c67fe4caa7',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => 'test'])
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_correctly_when_encoded_with_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => 'b9f912a4fc4b2952a48380579d3e4a1c55c0537ce583b3da7cc9f6c67fe4caa7',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => 'test'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_containing_a_ã_correctly_when_encoded_without_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => 'd35d92484222fce7e5c194381e5f53342caae6fa626cd61e3431bddc549b34e1',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => 'ã'])
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_containing_a_ã_correctly_when_encoded_with_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => 'd35d92484222fce7e5c194381e5f53342caae6fa626cd61e3431bddc549b34e1',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => 'ã'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_containing_a_好_correctly_when_encoded_without_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => '65ff94dce4894eb306a76ff0d397ec264b1c4980b57afbc3dd9526af242d239b',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => '好'])
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_containing_a_好_correctly_when_encoded_with_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => '65ff94dce4894eb306a76ff0d397ec264b1c4980b57afbc3dd9526af242d239b',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => '好'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_containing_a_uri_correctly_when_encoded_without_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => 'ebd68bfe7ed51c050fb92db098946cd21b7b23be6f682360a5e893840a1dc52f',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => 'https://localhost'])
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_simple_json_payload_containing_a_uri_correctly_when_encoded_with_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => 'ebd68bfe7ed51c050fb92db098946cd21b7b23be6f682360a5e893840a1dc52f',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(['test' => 'https://localhost'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_complex_json_payload_correctly_when_encoded_without_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => '0c3f0c81ba1fa3df9d3e0a1d72c4d491125153c0dea8355b6d48fe7ef1a4dacc',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(
                [
                    'users' => [
                        ['id' => 1, 'name' => 'Chris Hayes', 'email' => 'hayes@soapboxhq.com'],
                        ['id' => 2, 'name' => 'Jaspaul Bola', 'email' => 'jaspaul@soapboxhq.com'],
                        ['id' => 3, 'name' => 'Mr Penã 💩', 'email' => 'Mr-Penã@soapboxhq.com']
                    ]
                ]
            ),
            'https://localhost/poop'
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_complex_json_payload_correctly_when_encoded_with_flags()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => '0c3f0c81ba1fa3df9d3e0a1d72c4d491125153c0dea8355b6d48fe7ef1a4dacc',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(
                [
                    'users' => [
                        ['id' => 1, 'name' => 'Chris Hayes', 'email' => 'hayes@soapboxhq.com'],
                        ['id' => 2, 'name' => 'Jaspaul Bola', 'email' => 'jaspaul@soapboxhq.com'],
                        ['id' => 3, 'name' => 'Mr Penã 💩', 'email' => 'Mr-Penã@soapboxhq.com']
                    ]
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            'https://localhost/poop'
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }

    /**
     * @test
     */
    public function it_verifies_the_signature_of_a_complex_json_payload_after_stripping_the_trailing_slash()
    {
        $verifier = $this->makeSignedPostRequest(
            '303103f5-3dca-4704-96ad-860717769ec9',
            [
                'HTTP_X-SIGNED-TIMESTAMP' => '2001-01-01 00:00:00',
                'HTTP_Signature' => '0c3f0c81ba1fa3df9d3e0a1d72c4d491125153c0dea8355b6d48fe7ef1a4dacc',
                'HTTP_Algorithm' => 'sha256'
            ],
            json_encode(
                [
                    'users' => [
                        ['id' => 1, 'name' => 'Chris Hayes', 'email' => 'hayes@soapboxhq.com'],
                        ['id' => 2, 'name' => 'Jaspaul Bola', 'email' => 'jaspaul@soapboxhq.com'],
                        ['id' => 3, 'name' => 'Mr Penã 💩', 'email' => 'Mr-Penã@soapboxhq.com']
                    ]
                ]
            ),
            'https://localhost/poop/'
        );

        $verifier->setSignatureHeader('signature');
        $verifier->setAlgorithmHeader('algorithm');

        $this->assertTrue($verifier->isValid('key'));
        $this->assertFalse($verifier->isValid('not_key'));
    }
}
