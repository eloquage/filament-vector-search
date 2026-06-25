<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tables;

use Eloquage\FilamentVectorSearch\Support\EnumOptions;
use Eloquage\LaravelVectorSearch\Enums\IndexDriver;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

final class VectorIndexTable
{
    /**
     * @return array<int, TextColumn>
     */
    public static function columns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('driver')->badge(),
            TextColumn::make('embedding_provider')->badge(),
            TextColumn::make('embedding_model'),
            TextColumn::make('documents_count')->counts('documents')->label('Documents'),
        ];
    }

    /**
     * @return array<int, SelectFilter>
     */
    public static function filters(): array
    {
        return [
            SelectFilter::make('driver')
                ->options(EnumOptions::from(IndexDriver::cases())),
        ];
    }
}
