<?php

namespace SoapBox\SignedRequests\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExpiredRequestException extends Exception implements HttpExceptionInterface
{
    /**
     * The default exception message.
     *
     * @var string
     */
    const MESSAGE = 'The provided request has expired';

    /**
     * Provides a default error message for an expired request.
     *
     * @param string $message
     *        A customizable error message.
     */
    public function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }

    /**
     * Returns an HTTP BAD REQUEST status code.
     *
     * @return int
     *         An HTTP BAD REQUEST response status code
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
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
