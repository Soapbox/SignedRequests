<?php

namespace SoapBox\SignedRequests\Requests;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Psr7\Request;
use SoapBox\SignedRequests\Signature;
use SoapBox\SignedRequests\Configurations\Configuration;

class Generator
{
    /**
     * A configuration to use for generating signatures.
     *
     * @var \SoapBox\SignedRequests\Configurations\Configuration
     */
    private $configuration;

    /**
     * Constructs our signed request generator with an instance of the
     * configurations.
     *
     * @param \SoapBox\SignedRequests\Configurations\Configuration $configuration
     *        The configuration to use for generating the signed request.
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Signs and returns the request.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     *        The request to sign.
     *
     * @return \GuzzleHttp\Psr7\Request
     *         The request with an id, algorithm, and signature.
     */
    public function sign(Request $request) : Request
    {
        $algorithmHeader = $this->configuration->getAlgorithmHeader();
        $signatureHeader = $this->configuration->getSignatureHeader();

        $algorithm = $this->configuration->getSigningAlgorithm();
        $key = $this->configuration->getSigningKey();

        $request = $request->withHeader('X-SIGNED-ID', (string) Uuid::uuid4());
        $request = $request->withHeader(
            'X-SIGNED-TIMESTAMP',
            Carbon::now()->format('Y-m-d H:i:s')
        );

        $signature = new Signature(new Payload($request), $algorithm, $key);

        return $request
            ->withHeader($algorithmHeader, (string) $algorithm)
            ->withHeader($signatureHeader, (string) $signature);
    }
}
