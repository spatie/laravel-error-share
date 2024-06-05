<?php

use Illuminate\Foundation\Exceptions\Renderer\Exception as LaravelException;
use Illuminate\Foundation\Exceptions\Renderer\Listener;
use Illuminate\Foundation\Exceptions\Renderer\Mappers\BladeMapper;
use Illuminate\Http\Request;
use Spatie\LaravelErrorShare\Actions\MapLaravelStackTraceAction;
use Spatie\LaravelErrorShare\Actions\ResolveErrorSharePropertiesAction;
use Spatie\LaravelErrorShare\Tests\TestCase;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

uses(TestCase::class)->beforeEach(function (){
    // Long story short: the Laravel exception analysis code cannot handle Pest eval statements in the stack trace.
    app()->bind(MapLaravelStackTraceAction::class, fn() => new MapLaravelStackTraceAction(6));
})->in(__DIR__);


function createLaravelException(?Throwable $throwable = null, ?Request $request = null, ?Listener $listener = null): LaravelException
{
    $flattenException = app(BladeMapper::class)->map(
        app(HtmlErrorRenderer::class)->render($throwable ?? new Exception('RIP Ignition')),
    );

    return new LaravelException(
        $flattenException,
        $request ?? \request(),
        $listener ?? app(Illuminate\Foundation\Exceptions\Renderer\Listener::class),
        base_path()
    );
}
