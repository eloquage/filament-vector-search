<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChunksRelationManager extends RelationManager
{
    protected static string $relationship = 'chunks';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('view', $ownerRecord) ?? false;
    }

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
