<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Widgets;

use Eloquage\LaravelVectorSearch\Models\VectorChunk;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VectorEngineStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $queueSize = 0;

        if (config('queue.default') === 'database' && Schema::hasTable('jobs')) {
            $queueSize = (int) DB::table('jobs')->count();
        }

        return [
            Stat::make('Indexes', (string) VectorIndex::query()->count())
                ->description('Configured vector indexes')
                ->icon('heroicon-o-rectangle-stack'),
            Stat::make('Documents', (string) VectorDocument::query()->count())
                ->description('Indexed source documents')
                ->icon('heroicon-o-document-text'),
            Stat::make('Chunks', (string) VectorChunk::query()->count())
                ->description('Embedded content chunks')
                ->icon('heroicon-o-squares-2x2'),
            Stat::make('Queue', (string) $queueSize)
                ->description('Pending queue jobs')
                ->icon('heroicon-o-queue-list'),
        ];
    }
}
