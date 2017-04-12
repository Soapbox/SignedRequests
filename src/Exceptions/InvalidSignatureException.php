<?php

namespace SoapBox\SignedRequests\Exceptions;

use Exception;
use Throwable;

class InvalidSignatureException extends Exception
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
}
