<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChunksRelationManager extends RelationManager
{
    protected static string $relationship = 'chunks';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('chunk_index')->label('#')->sortable(),
                TextColumn::make('content')->limit(80),
                IconColumn::make('embedding')
                    ->label('Has vector')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->embedding)),
            ])
            ->defaultSort('chunk_index')
            ->paginated([10, 25, 50]);
    }
}
