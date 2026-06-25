<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Pages;

use BackedEnum;
use Eloquage\FilamentVectorSearch\Livewire\SearchPlayground;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use UnitEnum;

class VectorSearchDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static ?string $navigationLabel = 'Vector Search';

    protected static ?string $title = 'Vector Search Dashboard';

    protected static ?int $navigationSort = -1;

    protected static ?string $slug = 'vector-search';

    protected string $view = 'filament-vector-search::pages.vector-search-dashboard';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return VectorSearchNavigation::group();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->can('viewAny', VectorIndex::class)
            || $user->can('viewAny', VectorDocument::class);
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
                            ->description('Latest documents that failed during indexing.')
                            ->schema($this->recentFailureComponents())
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

    /**
     * @return array<int, Text|Html>
     */
    protected function recentFailureComponents(): array
    {
        $failures = $this->recentFailures();

        if ($failures->isEmpty()) {
            return [
                Text::make('No failed documents. Your pipeline looks healthy.'),
            ];
        }

        return $failures
            ->map(function (VectorDocument $document): Html {
                $editUrl = e(VectorDocumentResource::getUrl('edit', ['record' => $document]));
                $title = e((string) $document->title);
                $error = e(str((string) $document->error_log)->limit(120)->toString());

                return Html::make(new HtmlString(
                    "<p class=\"text-sm\"><a href=\"{$editUrl}\" class=\"font-medium text-primary-600 hover:underline dark:text-primary-400\">{$title}</a>: <span class=\"text-gray-600 dark:text-gray-300\">{$error}</span></p>"
                ));
            })
            ->all();
    }

    /**
     * @return Collection<int, VectorDocument>
     */
    protected function recentFailures(): Collection
    {
        return VectorDocument::query()
            ->where('status', DocumentStatus::FAILED)
            ->latest()
            ->limit(3)
            ->get();
    }
}
