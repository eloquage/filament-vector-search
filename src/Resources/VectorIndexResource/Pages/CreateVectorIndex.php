<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages;

use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVectorIndex extends CreateRecord
{
    protected static string $resource = VectorIndexResource::class;
}
