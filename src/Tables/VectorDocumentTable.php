<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tables;

use Eloquage\FilamentVectorSearch\Actions\CorrelateDocumentAction;
use Eloquage\FilamentVectorSearch\Actions\OpenManifestAction;
use Eloquage\FilamentVectorSearch\Support\ArchiveIntegration;
use Eloquage\FilamentVectorSearch\Support\EnumOptions;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;

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

        if ($configuration->includeArchiveColumns) {
            $columns[] = TextColumn::make('metadata.collection')
                ->label('Collection')
                ->toggleable();
            $columns[] = TextColumn::make('metadata.canonical_basename')
                ->label('Basename')
                ->toggleable();
            $columns[] = TextColumn::make('metadata.status')
                ->label('Archive status')
                ->badge()
                ->toggleable();
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
        $filters = [
            SelectFilter::make('status')
                ->options(EnumOptions::from(DocumentStatus::cases())),
            SelectFilter::make('vector_index_id')
                ->label('Index')
                ->relationship('vectorIndex', 'name')
                ->searchable()
                ->preload(),
        ];

        if (ArchiveIntegration::isAvailable()) {
            $filters[] = SelectFilter::make('metadata_collection')
                ->label('Collection')
                ->options(fn (): array => self::collectionFilterOptions())
                ->query(function ($query, array $data): void {
                    $value = $data['value'] ?? null;

                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $query->where('metadata->collection', $value);
                });
        }

        return $filters;
    }

    /**
     * @return array<int, BulkActionGroup>
     */
    public static function toolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                self::indexSelectedBulkAction(),
                self::reindexFailedBulkAction(),
            ]),
        ];
    }

    /**
     * @return array<int, Action>
     */
    public static function recordActions(): array
    {
        return [
            VectorDocumentTable::indexDocumentAction(),
            VectorDocumentTable::reindexDocumentAction(),
            OpenManifestAction::make(),
            CorrelateDocumentAction::makeRecordAction(),
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

    public static function reindexDocumentAction(): Action
    {
        return Action::make('reindexDocument')
            ->label('Re-index')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->requiresConfirmation()
            ->visible(fn (VectorDocument $record): bool => auth()->user()?->can('update', $record) === true
                && $record->status === DocumentStatus::FAILED)
            ->action(fn (VectorDocument $record) => self::dispatchReindex($record));
    }

    public static function indexSelectedBulkAction(): BulkAction
    {
        return BulkAction::make('indexSelected')
            ->label('Index selected')
            ->icon('heroicon-o-arrow-path')
            ->authorizeIndividualRecords('update')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                foreach ($records as $record) {
                    if (! $record instanceof VectorDocument) {
                        continue;
                    }

                    if ($record->status !== DocumentStatus::DRAFT) {
                        continue;
                    }

                    ProcessVectorDocument::dispatch($record);
                }
            });
    }

    public static function reindexFailedBulkAction(): BulkAction
    {
        return BulkAction::make('reindexFailed')
            ->label('Re-index failed')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->authorizeIndividualRecords('update')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                foreach ($records as $record) {
                    if (! $record instanceof VectorDocument) {
                        continue;
                    }

                    if ($record->status !== DocumentStatus::FAILED) {
                        continue;
                    }

                    self::dispatchReindex($record);
                }
            });
    }

    private static function dispatchReindex(VectorDocument $record): void
    {
        $record->update(['error_log' => null]);
        ProcessVectorDocument::dispatch($record);
    }

    /**
     * @return array<string, string>
     */
    private static function collectionFilterOptions(): array
    {
        return VectorDocument::query()
            ->whereNotNull('metadata')
            ->pluck('metadata')
            ->map(function (mixed $metadata): ?string {
                if (! is_array($metadata)) {
                    return null;
                }

                $collection = $metadata['collection'] ?? null;

                return is_string($collection) && $collection !== '' ? $collection : null;
            })
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $collection): array => [$collection => $collection])
            ->all();
    }
}
