<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase as Base;

abstract class TestCase extends Base
{
    /**
     * @after
     */
    protected function close_mockery()
    {
        Mockery::close();
    }
}
