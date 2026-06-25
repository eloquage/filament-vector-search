<?php

namespace Eloquage\FilamentVectorSearch\Tests;

use Eloquage\FilamentVectorSearch\FilamentVectorSearchServiceProvider;
use Eloquage\LaravelVectorSearch\LaravelVectorSearchServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LaravelVectorSearchServiceProvider::class,
            FilamentVectorSearchServiceProvider::class,
        ];
    }
}
