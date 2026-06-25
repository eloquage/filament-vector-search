<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages;

use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVectorIndex extends EditRecord
{
    protected static string $resource = VectorIndexResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
