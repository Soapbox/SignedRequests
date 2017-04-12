<?php

namespace Tests\Exceptions;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use SoapBox\SignedRequests\Exceptions\InvalidSignatureException;

class InvalidSignatureExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function an_invalid_signature_exception_is_constructed_with_a_default_message()
    {
        $exception = new InvalidSignatureException();
        $this->assertEquals(InvalidSignatureException::MESSAGE, $exception->getMessage());
    }

    /**
     * @test
     */
    public function the_message_for_the_exception_can_be_overwritten_during_construction()
    {
        $message = "So broken";
        $exception = new InvalidSignatureException($message);
        $this->assertNotEquals(InvalidSignatureException::MESSAGE, $exception->getMessage());
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function it_returns_a_bad_request_status_code()
    {
        $exception = new InvalidSignatureException();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_empty_set_of_response_headers()
    {
        $exception = new InvalidSignatureException();
        $this->assertEmpty($exception->getHeaders());
    }
}
