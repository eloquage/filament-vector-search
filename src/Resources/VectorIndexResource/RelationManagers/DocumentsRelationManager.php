<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\RelationManagers;

use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('chunks_count')->counts('chunks')->label('Chunks'),
                TextColumn::make('updated_at')->dateTime(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
