<?php

namespace SoapBox\SignedRequests\Middlewares\Guzzle;

use Psr\Http\Message\RequestInterface;
use SoapBox\SignedRequests\Requests\Generator;
use SoapBox\SignedRequests\Configurations\Configuration;

class GenerateSignature
{
    /**
     * An instance of the signed request generator
     *
     * @var \SoapBox\SignedRequests\Requests\Generator
     */
    protected $generator;

    /**
     * Expect a configuration to build our generator with.
     *
     * @param \SoapBox\SignedRequests\Configurations\Configuration $configuration
     *        The configuration to use for generating signed requests.
     */
    public function __construct(Configuration $configuration)
    {
        $this->generator = new Generator($configuration);
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
