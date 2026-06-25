<?php

use Eloquage\FilamentVectorSearch\Pages\VectorSearchDashboard;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;

it('registers header widgets on the dashboard', function () {
    $reflection = new ReflectionClass(VectorSearchDashboard::class);
    $method = $reflection->getMethod('getHeaderWidgets');
    $method->setAccessible(true);

    expect($method->invoke(new VectorSearchDashboard))->toBe([
        VectorEngineStatsWidget::class,
        IndexingPipelineWidget::class,
    ]);
});

it('uses the vector search dashboard slug', function () {
    expect(VectorSearchDashboard::getSlug())->toBe('vector-search');
});
