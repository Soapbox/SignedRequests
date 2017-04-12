<?php

namespace Tests\Requests;

use Tests\TestCase;
use Illuminate\Http\Request;
use SoapBox\SignedRequests\Requests\Signed;

class SignedTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_request()
    {
        $request = new Signed();
        $this->assertInstanceOf(Request::class, $request);
    }

    /**
     * @test
     */
    public function the_signature_header_key_can_be_set()
    {
        $request = new class() extends Signed {
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
        $request = new class() extends Signed {
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
     * A test helper to generate a signed request.
     *
     * @param  array $headers
     *         The request headers we'd like to include.
     * @param  string $content
     *         The content of the request.
     *
     * @return \SoapBox\SignedRequests\Requests\Signed
     *         A configured signed request.
     */
    protected function makeSignedRequest(array $headers = [], string $content = null) : Signed
    {
        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = $headers;

        return new Signed($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_signature_header_key_is_not_set()
    {
        $request = $this->makeSignedRequest([
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca'
        ], "payload");
        $request->setAlgorithmHeader('ALGORITHM');
        $this->assertFalse($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_algorithm_header_key_is_not_set()
    {
        $request = $this->makeSignedRequest([
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca'
        ], "payload");
        $request->setSignatureHeader('SIGNATURE');
        $this->assertFalse($request->isValid("key"));
    }

    /**
     * @test
     */
    public function a_signed_request_is_invalid_if_the_signature_header_is_not_set_on_the_request()
    {
        $request = $this->makeSignedRequest([
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
        $request = $this->makeSignedRequest([
            'HTTP_SIGNATURE' => '5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca'
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
        $request = $this->makeSignedRequest([
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => '5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca'
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
        $request = $this->makeSignedRequest([
            'HTTP_ALGORITHM' => 'sha256',
            'HTTP_SIGNATURE' => 'bd7affe8a72348b174610402219c16543165a188cba30e1d49062809a6d99b97'
        ], "{'payload': 'payload'}");
        $request->setAlgorithmHeader('ALGORITHM')
            ->setSignatureHeader('SIGNATURE');
        $this->assertTrue($request->isValid("key"));
    }
}
