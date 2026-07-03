<?php

namespace Spatie\LaravelErrorShare;

use Illuminate\Foundation\Exceptions\Renderer\Frame;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelErrorShareServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        if (! $this->exceptionRendererIsSupported()) {
            return;
        }

        if ($this->canIncludeViews()) {
            View::prependNamespace('laravel-exceptions-renderer', [__DIR__.'/../resources/views']);
        }
    }

    public function configurePackage(Package $package): void
    {
        $package->name('laravel-error-share');

        if (! $this->exceptionRendererIsSupported()) {
            return;
        }

        $package->hasConfigFile();

        if ($this->canIncludeViews()) {
            $package->hasViews();
        }
    }

    protected function exceptionRendererIsSupported(): bool
    {
        // The renderer API and blade components this package integrates with only exist on Laravel 12 and up.
        return method_exists(Frame::class, 'isMain');
    }

    protected function canIncludeViews(): bool
    {
        // Otherwise php artisan optimize may crash
        return config('app.debug') === true;
    }
}
