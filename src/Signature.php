<?php

namespace SoapBox\SignedRequests;

class Signature
{
    /**
     * HMAC signature.
     *
     * @var string
     */
    protected $signature;

    /**
     * Create a hmac signature from the payload
     *
     * @param string $payload
     *        The payload to sign.
     * @param string $algorithm
     *        The hmac algorithm to use.
     * @param string $key
     *        The key to use for hmac.
     */
    public function __construct(string $payload, string $algorithm, string $key)
    {
        $this->signature = hash_hmac($algorithm, $payload, $key);
    }

    /**
     * Checks if the two signatures are equal.
     *
     * @param  mixed $signature
     *         The signature to check for equality.
     *
     * @return bool
     *         Returns true if the two signatures are equal, false otherwise.
     */
    public function equals($signature) : bool
    {
        if ($signature instanceof Signature) {
            $signature = $signature->signature;
        }

        if ($this === $signature || $this->signature == $signature) {
            return true;
        }

        return false;
    }
}
