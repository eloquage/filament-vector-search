<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Pages;

use BackedEnum;
use Eloquage\FilamentVectorSearch\Livewire\SearchPlayground;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class VectorSearchDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'Vector Search';

    protected static ?string $title = 'Vector Search Dashboard';

    protected static ?int $navigationSort = -1;

    protected static ?string $slug = 'vector-search';

    protected string $view = 'filament-vector-search::pages.vector-search-dashboard';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return VectorSearchNavigation::group();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Semantic search')
                            ->description('Query your knowledge base in real time.')
                            ->schema([
                                Livewire::make(SearchPlayground::class),
                            ])
                            ->columnSpanFull(),
                        Section::make('Recent failures')
                            ->schema([
                                Section::make()
                                    ->heading('Latest indexing errors')
                                    ->description($this->recentFailuresDescription()),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            VectorEngineStatsWidget::class,
            IndexingPipelineWidget::class,
        ];
    }

    protected function recentFailuresDescription(): string
    {
        $failures = VectorDocument::query()
            ->where('status', DocumentStatus::FAILED)
            ->latest()
            ->limit(3)
            ->get();

        if ($failures->isEmpty()) {
            return 'No failed documents. Your pipeline looks healthy.';
        }

        return $failures
            ->map(fn (VectorDocument $document): string => "{$document->title}: ".str($document->error_log)->limit(120))
            ->implode("\n");
    }
}
