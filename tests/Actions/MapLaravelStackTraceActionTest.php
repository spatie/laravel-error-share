<?php

use Spatie\LaravelErrorShare\Actions\MapLaravelExceptionAction;
use Spatie\LaravelErrorShare\Tests\Stubs\ExceptionFactory;

it('will map a stack trace', function () {
    $mapped = resolve(MapLaravelExceptionAction::class)->execute(
        createLaravelException((new ExceptionFactory())->execute())
    );

    expect($mapped['stacktrace'][0])->toBeArray();
    expect($mapped['stacktrace'][0]['file'])->toBe(realpath(__DIR__.'/../Stubs/ExceptionFactory.php'));
    expect($mapped['stacktrace'][0]['line_number'])->toBe(9);
    expect($mapped['stacktrace'][0]['method'])->toBe('execute');
    //expect($mapped['stacktrace'][0]['class'])->toBe(ExceptionFactory::class);
    expect($mapped['stacktrace'][0]['code_snippet'])
        ->toBeArray()
        ->toHaveKey(4)
        ->toHaveKey(12);
    expect($mapped['stacktrace'][0]['is_application_frame'])->toBeFalse();

    expect($mapped['stacktrace'][1])->toBeArray();
    expect($mapped['stacktrace'][1]['file'])->toBe(__FILE__);
    expect($mapped['stacktrace'][1]['line_number'])->toBe(__LINE__ - 16);
    expect($mapped['stacktrace'][1]['method'])->toStartWith('{closure');
    expect($mapped['stacktrace'][1]['class'])->toBeNull();
    expect($mapped['stacktrace'][1]['code_snippet'])
        ->toBeArray()
        ->toHaveKey(__LINE__ - 25)
        ->toHaveKey(__LINE__ - 15);
    expect($mapped['stacktrace'][1]['is_application_frame'])->toBeFalse();
});
