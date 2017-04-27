<?php

namespace Tests\Exceptions;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use SoapBox\SignedRequests\Exceptions\ExpiredRequestException;

class ExpiredRequestExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function an_invalid_signature_exception_is_constructed_with_a_default_message()
    {
        $exception = new ExpiredRequestException();
        $this->assertEquals(ExpiredRequestException::MESSAGE, $exception->getMessage());
    }

    /**
     * @test
     */
    public function the_message_for_the_exception_can_be_overwritten_during_construction()
    {
        $message = "So broken";
        $exception = new ExpiredRequestException($message);
        $this->assertNotEquals(ExpiredRequestException::MESSAGE, $exception->getMessage());
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function it_returns_a_bad_request_status_code()
    {
        $exception = new ExpiredRequestException();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_empty_set_of_response_headers()
    {
        $exception = new ExpiredRequestException();
        $this->assertEmpty($exception->getHeaders());
    }
}
