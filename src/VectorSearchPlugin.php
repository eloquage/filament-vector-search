<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch;

use Eloquage\FilamentVectorSearch\Pages\VectorSearchDashboard;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;
use Filament\Contracts\Plugin;
use Filament\Panel;

class VectorSearchPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-vector-search';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                VectorSearchDashboard::class,
            ])
            ->resources([
                VectorIndexResource::class,
                VectorDocumentResource::class,
            ])
            ->widgets([
                VectorEngineStatsWidget::class,
                IndexingPipelineWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
