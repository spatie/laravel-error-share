<?php

use Illuminate\Foundation\AliasLoader;

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('only registers classes that exist for package discovery', function () {
    $laravel = json_decode(file_get_contents(__DIR__.'/../composer.json'), true)['extra']['laravel'];

    foreach ([...$laravel['providers'] ?? [], ...array_values($laravel['aliases'] ?? [])] as $class) {
        expect(class_exists($class))->toBeTrue("{$class} does not exist");
    }
});

it('allows looking up the absent LaravelErrorShare class', function () {
    $laravel = json_decode(file_get_contents(__DIR__.'/../composer.json'), true)['extra']['laravel'];
    $loader = AliasLoader::getInstance();
    $originalAliases = $loader->getAliases();

    try {
        AliasLoader::getInstance($laravel['aliases'] ?? [])->register();

        expect(class_exists('LaravelErrorShare'))->toBeFalse()
            ->and(property_exists('LaravelErrorShare', 'anything'))->toBeFalse()
            ->and(class_exists('Route'))->toBeTrue();
    } finally {
        $loader->setAliases($originalAliases);
    }
});
