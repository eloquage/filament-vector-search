<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Widgets;

use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IndexingPipelineWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Indexing pipeline';

    protected function getStats(): array
    {
        return collect(DocumentStatus::cases())
            ->map(function (DocumentStatus $status): Stat {
                $count = VectorDocument::query()->where('status', $status)->count();

                return Stat::make($status->getLabel() ?? $status->value, (string) $count)
                    ->description('Documents in this stage');
            })
            ->all();
    }
}
