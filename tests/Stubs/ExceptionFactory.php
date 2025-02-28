<?php

namespace Spatie\LaravelErrorShare\Tests\Stubs;

class ExceptionFactory
{
    public function execute()
    {
        return new TestException;
    }
}
