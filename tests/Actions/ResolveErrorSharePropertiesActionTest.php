<?php

use Illuminate\Foundation\Exceptions\Renderer\Exception;
use Spatie\LaravelErrorShare\Actions\MapLaravelExceptionAction;
use Spatie\LaravelErrorShare\Actions\ResolveErrorSharePropertiesAction;
use Spatie\LaravelErrorShare\Tests\Stubs\TestException;

it('can get properties', function () {
    $exception = createLaravelException(new TestException);

    $properties = app(ResolveErrorSharePropertiesAction::class)->execute($exception);

    expect((string) $properties['url'])->toBe('https://flareapp.io/api/public-reports');
    expect($properties['report'])->toBeArray();
});

it('will handle a mapping error gracefully', function () {
    $exception = createLaravelException(new TestException);

    app()->bind(MapLaravelExceptionAction::class, fn () => new class extends MapLaravelExceptionAction
    {
        public function __construct()
        {
            // We're fake
        }

        public function execute(Exception $exception): array
        {
            throw new \Exception('Something went wrong');
        }
    });

    $properties = app(ResolveErrorSharePropertiesAction::class)->execute($exception);

    expect($properties['error'])->toBeString();
});
