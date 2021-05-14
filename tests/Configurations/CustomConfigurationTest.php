<?php

namespace Tests\Configurations;

use Tests\TestCase;
use SoapBox\SignedRequests\Configurations\CustomConfiguration;

class CustomConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_set_values_when_requested()
    {
        $algorithmHeader = 'a';
        $signatureHeader = 's';
        $signingAlgorithm = 'sa';
        $signingKey = 'sk';

        $configuration = new CustomConfiguration(
            $algorithmHeader,
            $signatureHeader,
            $signingAlgorithm,
            $signingKey
        );

        $this->assertSame($algorithmHeader, $configuration->getAlgorithmHeader());
        $this->assertSame($signatureHeader, $configuration->getSignatureHeader());
        $this->assertSame($signingAlgorithm, $configuration->getSigningAlgorithm());
        $this->assertSame($signingKey, $configuration->getSigningKey());
    }
}
