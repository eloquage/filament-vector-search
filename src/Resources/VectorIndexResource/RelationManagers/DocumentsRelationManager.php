<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\RelationManagers;

use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTable;
use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTableConfiguration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function table(Table $table): Table
    {
        return $table
            ->columns(VectorDocumentTable::columns(VectorDocumentTableConfiguration::forRelationManager()))
            ->defaultSort('updated_at', 'desc');
    }
}
