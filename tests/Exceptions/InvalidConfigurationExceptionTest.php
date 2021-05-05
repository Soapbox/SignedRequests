<?php

namespace Tests\Exceptions;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use SoapBox\SignedRequests\Exceptions\InvalidConfigurationException;

class InvalidConfigurationExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function an_invalid_configuration_exception_is_constructed_with_a_default_message()
    {
        $exception = new InvalidConfigurationException();
        $this->assertEquals(InvalidConfigurationException::MESSAGE, $exception->getMessage());
    }

    /**
     * @test
     */
    public function the_message_for_the_exception_can_be_overwritten_during_construction()
    {
        $message = "So broken";
        $exception = new InvalidConfigurationException($message);
        $this->assertNotEquals(InvalidConfigurationException::MESSAGE, $exception->getMessage());
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function it_returns_a_bad_request_status_code()
    {
        $exception = new InvalidConfigurationException();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_empty_set_of_response_headers()
    {
        $exception = new InvalidConfigurationException();
        $this->assertEmpty($exception->getHeaders());
    }
}
