<?php

namespace Tests;

use SoapBox\SignedRequests\Signature;

class SignatureTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $signature = new Signature("payload", "sha256", "key");

        $this->assertInstanceOf(Signature::class, $signature);
    }

    /**
     * @test
     */
    public function a_signature_should_be_equal_to_itself()
    {
        $signature = new Signature("payload", "sha256", "key");

        $this->assertTrue($signature->equals($signature));
    }

    /**
     * @test
     */
    public function two_signatures_with_the_same_payload_algorithm_and_key_should_be_equal()
    {
        $signature = new Signature("payload", "sha256", "key");
        $alternative = new Signature("payload", "sha256", "key");

        $this->assertTrue($signature->equals($alternative));
    }

    /**
     * @test
     */
    public function a_signature_should_be_equal_to_the_hmac_representation_of_the_signature()
    {
        $signature = new Signature("payload", "sha256", "key");
        $hmac = "5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca";

        $this->assertTrue($signature->equals($hmac));
    }

    /**
     * @test
     */
    public function two_signatures_with_different_algorithms_should_not_be_equal()
    {
        $algorithm1 = new Signature("payload", "sha256", "key");
        $algorithm2 = new Signature("payload", "sha512", "key");

        $this->assertFalse($algorithm1->equals($algorithm2));
    }

    /**
     * @test
     */
    public function two_signatures_signed_with_different_keys_should_not_be_equal()
    {
        $key1 = new Signature("payload", "sha256", "key1");
        $key2 = new Signature("payload", "sha256", "key2");

        $this->assertFalse($key1->equals($key2));
    }

    /**
     * @test
     */
    public function two_signatures_with_different_payloads_should_not_be_equal()
    {
        $payload1 = new Signature("payload1", "sha256", "key");
        $payload2 = new Signature("payload2", "sha256", "key");

        $this->assertFalse($payload1->equals($payload2));
    }

    /**
     * @test
     */
    public function a_signature_should_not_match_a_class_containing_the_correct_hmac_if_it_is_not_a_signature()
    {
        $signature = new Signature("payload", "sha256", "key");
        $fake = new class() {
            public $signature = "5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca";
        };

        $this->assertFalse($signature->equals($fake));
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_a_string()
    {
        $signature = new Signature("payload", "sha256", "key");

        $this->assertEquals(
            "5d98b45c90a207fa998ce639fea6f02ecc8cc3f36fef81d694fb856b4d0a28ca",
            (string) $signature
        );
    }
}
