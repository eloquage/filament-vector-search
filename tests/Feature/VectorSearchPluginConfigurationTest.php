<?php

use Eloquage\FilamentVectorSearch\Pages\VectorSearchDashboard;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\FilamentVectorSearch\VectorSearchPlugin;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Resources\Resource;

it('registers the vector search plugin', function () {
    expect(VectorSearchPlugin::make())
        ->toBeInstanceOf(VectorSearchPlugin::class)
        ->and(VectorSearchPlugin::make()->getId())
        ->toBe('filament-vector-search');
});

it('sets a custom navigation group through the fluent api', function () {
    VectorSearchPlugin::make()
        ->navigationGroup('Knowledge Base')
        ->register(Filament::getPanel('admin'));

    expect(VectorSearchNavigation::group())->toBe('Knowledge Base');
    expect(VectorIndexResource::getNavigationGroup())->toBe('Knowledge Base');
    expect(VectorDocumentResource::getNavigationGroup())->toBe('Knowledge Base');
    expect(VectorSearchDashboard::getNavigationGroup())->toBe('Knowledge Base');
});

it('rejects invalid dashboard page classes', function () {
    $plugin = VectorSearchPlugin::make()->dashboardPage(Resource::class);

    $reflection = new ReflectionClass($plugin);
    $method = $reflection->getMethod('resolveDashboardPage');
    $method->setAccessible(true);

    expect(fn () => $method->invoke($plugin))
        ->toThrow(\InvalidArgumentException::class);
});

it('rejects invalid widget classes', function () {
    $plugin = VectorSearchPlugin::make()->widgets([Page::class]);

    $reflection = new ReflectionClass($plugin);
    $method = $reflection->getMethod('resolveWidgets');
    $method->setAccessible(true);

    expect(fn () => $method->invoke($plugin))
        ->toThrow(\InvalidArgumentException::class);
});

it('registers custom widgets through the fluent api', function () {
    $plugin = VectorSearchPlugin::make()->widgets([
        VectorEngineStatsWidget::class,
    ]);

    $reflection = new ReflectionClass($plugin);
    $method = $reflection->getMethod('resolveWidgets');
    $method->setAccessible(true);

    expect($method->invoke($plugin))->toBe([
        VectorEngineStatsWidget::class,
    ]);
});

it('defaults to the package widgets when none are configured', function () {
    $plugin = VectorSearchPlugin::make();

    $reflection = new ReflectionClass($plugin);
    $method = $reflection->getMethod('resolveWidgets');
    $method->setAccessible(true);

    expect($method->invoke($plugin))->toBe([
        VectorEngineStatsWidget::class,
        IndexingPipelineWidget::class,
    ]);
});
