<?php

namespace Spatie\LaravelErrorShare;

use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelErrorShareServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        View::prependNamespace('laravel-exceptions-renderer', [__DIR__.'/../resources/views']);

        return parent::register();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-error-share')
            ->hasConfigFile()
            ->hasViews();
    }
}
