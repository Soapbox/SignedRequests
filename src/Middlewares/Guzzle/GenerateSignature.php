<?php

namespace SoapBox\SignedRequests\Middlewares\Guzzle;

use Psr\Http\Message\RequestInterface;
use SoapBox\SignedRequests\Requests\Generator;

class GenerateSignature
{
    /**
     * An instance of the signed request generator
     *
     * @var \SoapBox\SignedRequests\Requests\Generator
     */
    protected $generator;

    /**
     * Expect an instance of the generator so we can sign the request
     *
     * @param \SoapBox\SignedRequests\Requests\Generator $generator
     *        An instance of the signed request generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Return the middleware callable. This callable function signs the request.
     *
     * @param callable $handler
     *        The next handler to invoke
     *
     * @return callable
     */
    public function __invoke(callable $handler) : callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $this->generator->sign($request);
            return $handler($request, $options);
        };
    }
}
