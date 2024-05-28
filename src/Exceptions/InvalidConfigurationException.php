<?php

namespace SoapBox\SignedRequests\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class InvalidConfigurationException extends Exception implements HttpExceptionInterface
{
    /**
     * The default exception message.
     *
     * @var string
     */
    const MESSAGE = 'Failed to find Signed Requests configuration key';

    /**
     * Provides a default error message for an invalid configuration.
     *
     * @param string $message
     *        A customizable error message.
     */
    public function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }

    /**
     * Returns an HTTP UNPROCESSABLE ENTITY status code.
     *
     * @return int
     *         An HTTP UNPROCESSABLE ENTITY response status code
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /**
     * Returns response headers.
     *
     * @return array
     *         Response headers
     */
    public function getHeaders(): array
    {
        return [];
    }
}
