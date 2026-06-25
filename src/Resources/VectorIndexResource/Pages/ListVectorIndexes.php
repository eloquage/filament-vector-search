<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages;

use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVectorIndexes extends ListRecords
{
    protected static string $resource = VectorIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
