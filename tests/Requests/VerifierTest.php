<?php

namespace Tests\Requests;

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
        $request =
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
            'HTTP_X-SIGNED-ID' => $id
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
            'HTTP_SIGNATURE' => '182a082fb62f56f0c9df13bb3d7e478e9384515806fcfb2802e3fe88b3cb1e92'
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
            'HTTP_SIGNATURE' => '182a082fb62f56f0c9df13bb3d7e478e9384515806fcfb2802e3fe88b3cb1e92'
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
            'HTTP_SIGNATURE' => '182a082fb62f56f0c9df13bb3d7e478e9384515806fcfb2802e3fe88b3cb1e92'
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
            'HTTP_SIGNATURE' => '182a082fb62f56f0c9df13bb3d7e478e9384515806fcfb2802e3fe88b3cb1e92'
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
            'HTTP_SIGNATURE' => '935ff9e99c8f66c538332fa558a9132b6ec7b04ad018e88571024c1f8af8e9fc'
        ], "{\"payload\": \"payload\"}");
        $request->setAlgorithmHeader('ALGORITHM')
            ->setSignatureHeader('SIGNATURE');
        $this->assertTrue($request->isValid("key"));
    }
}
