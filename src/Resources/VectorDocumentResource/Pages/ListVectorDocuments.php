<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages;

use Eloquage\FilamentVectorSearch\Actions\CorrelateDocumentAction;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVectorDocuments extends ListRecords
{
    protected static string $resource = VectorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CorrelateDocumentAction::makeHeaderAction(),
            Action::make('reindexAllFailed')
                ->label('Re-index all failed')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->requiresConfirmation()
                ->visible(fn (): bool => VectorDocument::query()->where('status', DocumentStatus::FAILED)->exists())
                ->action(function (): void {
                    VectorDocument::query()
                        ->where('status', DocumentStatus::FAILED)
                        ->get()
                        ->filter(fn (VectorDocument $document): bool => auth()->user()?->can('update', $document) === true)
                        ->each(function (VectorDocument $document): void {
                            $document->update(['error_log' => null]);
                            ProcessVectorDocument::dispatch($document);
                        });
                }),
            CreateAction::make(),
        ];
    }
}
