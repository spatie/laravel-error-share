<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('only registers classes that exist for package discovery', function () {
    $laravel = json_decode(file_get_contents(__DIR__.'/../composer.json'), true)['extra']['laravel'];

    foreach ([...$laravel['providers'] ?? [], ...array_values($laravel['aliases'] ?? [])] as $class) {
        expect(class_exists($class))->toBeTrue("{$class} does not exist");
    }
});
