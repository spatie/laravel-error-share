<?php

namespace Spatie\LaravelErrorShare\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelErrorShare\LaravelErrorShareServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\LaravelErrorShare\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelErrorShareServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        Schema::create('users', function (Blueprint $blueprint) {
            $blueprint->increments('id');

            $blueprint->string('name');

            $blueprint->datetimes();
        });
    }
}
