<?php

namespace Tests;

use Carbon\Carbon;
use SoapBox\SignedRequests\Helpers;

class HelpersTest extends TestCase
{
    /**
     * @test
     */
    public function it_correctly_verifies_that_the_provided_datetime_string_is_in_the_given_format()
    {
        $datetime = Carbon::parse('2001-01-31 12:11:18');

        $this->assertTrue(Helpers::verifyDateTime((string) $datetime, 'Y-m-d H:i:s'));
        $this->assertTrue(Helpers::verifyDateTime($datetime->format('Y-m-d H:i:s'), 'Y-m-d H:i:s'));
        $this->assertTrue(Helpers::verifyDateTime($datetime->format('Y-m-d H:i:s.u'), 'Y-m-d H:i:s.u'));
        $this->assertTrue(Helpers::verifyDateTime($datetime->format('Y-m-d'), 'Y-m-d'));
        $this->assertTrue(Helpers::verifyDateTime($datetime->format('Y-d-m H:i:s'), 'Y-d-m H:i:s'));

        $this->assertFalse(Helpers::verifyDateTime($datetime->format('Y-m-d H:i:s.u'), 'Y-m-d H:i:s'));
        $this->assertFalse(Helpers::verifyDateTime($datetime->format('Y-m-d H-i-s'), 'Y-m-d H:i:s.u'));
        $this->assertFalse(Helpers::verifyDateTime($datetime->format('Y-m-d H:i:s'), 'Y-d-m H:i:s'));
        $this->assertFalse(Helpers::verifyDateTime($datetime->format('Y-m-d H:i:s'), 'Y-m-d'));
        $this->assertFalse(Helpers::verifyDateTime($datetime->format('Y-m-d H:i:s'), 'Y-m-d-H:i:s.u'));
    }
}
