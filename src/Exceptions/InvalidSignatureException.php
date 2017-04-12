<?php

namespace SoapBox\SignedRequests\Exceptions;

use Exception;
use Throwable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class InvalidSignatureException extends Exception implements HttpExceptionInterface
{
    /**
     * The default exception message.
     *
     * @var string
     */
    const MESSAGE = 'The provided signature was not valid';

    /**
     * Provides a default error message for an invalid signature.
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
    public function getStatusCode()
    {
        return Response::HTTP_BAD_REQUEST;
    }

    /**
     * Returns response headers.
     *
     * @return array
     *         Response headers
     */
    public function getHeaders()
    {
        return [];
    }
}
