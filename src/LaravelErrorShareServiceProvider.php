<?php

namespace Spatie\LaravelErrorShare;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use Spatie\LaravelErrorShare\Components\Header;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelErrorShare\Commands\LaravelErrorShareCommand;

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
