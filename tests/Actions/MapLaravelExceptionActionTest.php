<?php

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelErrorShare\Actions\MapLaravelExceptionAction;
use Spatie\LaravelErrorShare\Tests\Stubs\TestException;

it('will map a laravel exception', function () {
    $mapped = resolve(MapLaravelExceptionAction::class)->execute(
        createLaravelException($exception = new TestException())
    );

    expect($mapped)->toBeArray();
    expect($mapped['notifier'])->toBe('laravel-error-share');
    expect($mapped['exception_class'])->toBe(TestException::class);
    expect($mapped['seen_at'])->toBeInt();
    expect($mapped['application_path'])->toBe(app_path());
    expect($mapped['message'])->toBe($exception->getMessage());
    expect($mapped['language'])->toBe('PHP');
    expect($mapped['language_version'])->toBe(phpversion());
    expect($mapped['framework_version'])->toBe(app()->version());
    expect($mapped['application_version'])->toBeNull();
    expect($mapped['stage'])->toBe(app()->environment());
    expect($mapped['message_level'])->toBeNull();
    expect($mapped['open_frame_index'])->toBe(0);
    expect($mapped['glows'])->toBeArray()->toBeEmpty();
    expect($mapped['solutions'])->toBeArray()->toBeEmpty();
    expect($mapped['context'])->toBeArray();
    expect($mapped['stacktrace'])->toBeArray();
    expect($mapped['documentation_links'])->toBeArray()->toBeEmpty();
    expect($mapped['tracking_uuid'])->toBeNull();
    expect($mapped['handled'])->toBeNull();
});

it('will map queries made', function () {
    $listener = app(Illuminate\Foundation\Exceptions\Renderer\Listener::class);

    $listener->registerListeners(
        app(Dispatcher::class)
    );

    DB::table('users')->get();

    $properties = resolve(MapLaravelExceptionAction::class)->execute(
        createLaravelException(new TestException(), listener: $listener)
    );

    expect($properties['context']['queries'])->toBeArray()->toHaveCount(1);

    $query = $properties['context']['queries'][0];

    expect($query['sql'])->toBe('select * from "users"');
    expect($query['bindings'])->toBeArray()->toBeEmpty();
    expect($query['time'])->toBeFloat();
    expect($query['connection_name'])->toBe(DB::connection()->getName());
    expect($query['microtime'])->toBeNull();
});

it('will map request info', function () {
    Route::post('/hi-there', function () {
        return resolve(MapLaravelExceptionAction::class)->execute(
            createLaravelException(new TestException())
        );
    });

    $properties = $this->post(
        uri: '/hi-there',
        data: $data = [
            'some-data' => 42,
        ],
        headers: [
            'X-KEY' => '123'
        ]
    )->json();

    expect($properties['context']['request']['url'])->toBe('http://localhost/hi-there');
    expect($properties['context']['request']['ip'])->toBe("127.0.0.1");
    expect($properties['context']['request']['method'])->toBe('POST');
    expect($properties['context']['request']['useragent'])->toBeString();

    expect($properties['context']['request_data']['body'])->toEqual($data);

    expect($properties['context']['headers'])
        ->toBeArray()
        ->toHaveKey('x-key', '123');
});

it('will map route info', function () {
    Route::get('/route/{id}', function (int $id) {
        return resolve(MapLaravelExceptionAction::class)->execute(
            createLaravelException(new TestException())
        );
    })->name('route');

    $properties = $this->get('/route/69')->json();

    expect($properties['context']['route'])->toBeArray()->toHaveKey('route', 'route');
    expect($properties['context']['route'])->toHaveKey('controllerAction', 'Closure');
    expect($properties['context']['route']['routeParameters'])->toHaveKey('id', 69);
});
