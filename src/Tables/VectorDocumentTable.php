<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tables;

use Eloquage\FilamentVectorSearch\Support\EnumOptions;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

final class VectorDocumentTable
{
    /**
     * @return array<int, TextColumn>
     */
    public static function columns(VectorDocumentTableConfiguration $configuration): array
    {
        $columns = [];

        $titleColumn = TextColumn::make('title');

        if ($configuration->searchableTitle) {
            $titleColumn->searchable();
        }

        if ($configuration->sortableTitle) {
            $titleColumn->sortable();
        }

        $columns[] = $titleColumn;

        if ($configuration->includeIndexColumn) {
            $columns[] = TextColumn::make('vectorIndex.name')
                ->label('Index')
                ->sortable();
        }

        $columns[] = TextColumn::make('status')->badge();
        $columns[] = TextColumn::make('chunks_count')->counts('chunks')->label('Chunks');

        $updatedAtColumn = TextColumn::make('updated_at')->dateTime();

        if ($configuration->sortableUpdatedAt) {
            $updatedAtColumn->sortable();
        }

        $columns[] = $updatedAtColumn;

        return $columns;
    }

    /**
     * @return array<int, SelectFilter>
     */
    public static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->options(EnumOptions::from(DocumentStatus::cases())),
            SelectFilter::make('vector_index_id')
                ->label('Index')
                ->relationship('vectorIndex', 'name')
                ->searchable()
                ->preload(),
        ];
    }

    public static function indexDocumentAction(): Action
    {
        return Action::make('indexDocument')
            ->label('Index document')
            ->icon('heroicon-o-arrow-path')
            ->visible(fn (VectorDocument $record): bool => auth()->user()?->can('update', $record) === true
                && $record->status === DocumentStatus::DRAFT)
            ->action(fn (VectorDocument $record) => ProcessVectorDocument::dispatch($record));
    }
}
