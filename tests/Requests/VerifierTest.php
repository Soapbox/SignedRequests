<?php

namespace Tests\Requests;

use Carbon\Carbon;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
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
            'HTTP_X-SIGNED-TIMESTAMP' => Carbon::parse('2017-10-10 12:00:00')
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
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '9c44bdc4bac17149f3aba3778d74a9e217a41446b723efc5c3c903c557ba466e'
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
        $id = "363c60de-9024-4915-99a9-88d63167665e";

        $request = $this->makeSignedRequest($id, [
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '60ec59a169fc1cc9373ed38f8b9783ba2ad5a6782945058d25898fab410927de'
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
}
