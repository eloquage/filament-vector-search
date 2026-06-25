<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Widgets;

use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class IndexingPipelineWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Indexing pipeline';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $counts = VectorDocument::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(DocumentStatus::cases())
            ->map(function (DocumentStatus $status) use ($counts): Stat {
                $count = (int) ($counts[$status->value] ?? 0);

                return Stat::make($status->getLabel(), (string) $count)
                    ->description('Documents in this stage');
            })
            ->all();
    }
}
