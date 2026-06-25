<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch;

use Eloquage\FilamentVectorSearch\Livewire\SearchPlayground;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentVectorSearchServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-vector-search';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component('filament-vector-search.search-playground', SearchPlayground::class);
    }
}
