<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages;

use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVectorDocuments extends ListRecords
{
    protected static string $resource = VectorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
