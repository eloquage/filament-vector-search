<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages;

use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVectorDocument extends EditRecord
{
    protected static string $resource = VectorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('indexDocument')
                ->label('Index document')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => $this->record->status === DocumentStatus::DRAFT)
                ->action(fn () => ProcessVectorDocument::dispatch($this->record)),
            DeleteAction::make(),
        ];
    }
}
