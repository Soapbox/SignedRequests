<?php

namespace SoapBox\SignedRequests\Requests;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Psr7\Request;
use SoapBox\SignedRequests\Signature;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Config\Repository;

class Generator
{
    /**
     * An instance of the configuration repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $repository;

    /**
     * Constructs our signed request generator with an instance of the
     * configurations.
     *
     * @param \Illuminate\Contracts\Config\Repository $repository
     *        A configuration repository.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Signs and returns the request.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     *        The request to sign.
     *
     * @return \GuzzleHttp\Psr7\Request
     *         The request with an id, algorith, and signature.
     */
    public function sign(Request $request) : Request
    {
        $algorithmHeader = $this->repository->get('signed-requests.headers.algorithm');
        $signatureHeader = $this->repository->get('signed-requests.headers.signature');

        $algorithm = $this->repository->get('signed-requests.algorithm');
        $key = $this->repository->get('signed-requests.key');

        $request = $request->withHeader('X-SIGNED-ID', (string) Uuid::uuid4());
        $request = $request->withHeader('X-SIGNED-TIMESTAMP', (string) Carbon::now());

        $signature = new Signature(new Payload($request), $algorithm, $key);

        return $request
            ->withHeader($algorithmHeader, (string) $algorithm)
            ->withHeader($signatureHeader, (string) $signature);
    }
}
