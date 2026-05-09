<?php

use function Spatie\Snapshots\assertMatchesFileSnapshot;

it('still is the same laravel topbar blade component', function () {
    assertMatchesFileSnapshot(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/resources/exceptions/renderer/components/topbar.blade.php');
})->skipOnWindows();

it('still is the same laravel show blade view', function () {
    assertMatchesFileSnapshot(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/resources/exceptions/renderer/show.blade.php');
})->skipOnWindows();
