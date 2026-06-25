<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages;

use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVectorDocument extends CreateRecord
{
    protected static string $resource = VectorDocumentResource::class;
}
