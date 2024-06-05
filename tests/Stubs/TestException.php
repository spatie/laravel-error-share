<?php

namespace Spatie\LaravelErrorShare\Tests\Stubs;

use Exception;

class TestException extends Exception
{
    public function __construct()
    {
        parent::__construct("RIP Ignition");
    }
}
