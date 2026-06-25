<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\RelationManagers;

use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTable;
use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTableConfiguration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('view', $ownerRecord) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns(VectorDocumentTable::columns(VectorDocumentTableConfiguration::forRelationManager()))
            ->defaultSort('updated_at', 'desc');
    }
}
