<?php

namespace Spatie\LaravelErrorShare;

use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelErrorShareServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        View::prependNamespace('laravel-exceptions-renderer', [__DIR__.'/../resources/views']);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-error-share')
            ->hasConfigFile()
            ->hasViews();
    }
}
