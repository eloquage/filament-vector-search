<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources;

use BackedEnum;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\RelationManagers\DocumentsRelationManager;
use Eloquage\FilamentVectorSearch\Support\EnumOptions;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\FilamentVectorSearch\Tables\VectorIndexTable;
use Eloquage\LaravelVectorSearch\Enums\ChunkingStrategy;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Enums\IndexDriver;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VectorIndexResource extends Resource
{
    protected static ?string $model = VectorIndex::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return VectorSearchNavigation::group();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Index configuration')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('driver')
                            ->options(EnumOptions::from(IndexDriver::cases()))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if ($state === IndexDriver::MEILISEARCH->value) {
                                    $set('dimensions', null);
                                }
                            }),
                        Select::make('embedding_provider')
                            ->options(EnumOptions::from(EmbeddingProvider::cases()))
                            ->required()
                            ->live(),
                        TextInput::make('embedding_model')
                            ->required()
                            ->placeholder('e.g. text-embedding-3-small'),
                        TextInput::make('dimensions')
                            ->numeric()
                            ->visible(fn (Get $get): bool => $get('driver') === IndexDriver::POSTGRES_VECTOR->value),
                        Select::make('chunking_strategy')
                            ->options(EnumOptions::from(ChunkingStrategy::cases()))
                            ->required(),
                        TextInput::make('chunk_size')
                            ->numeric()
                            ->default(1000)
                            ->required(),
                        TextInput::make('chunk_overlap')
                            ->numeric()
                            ->default(200)
                            ->visible(fn (Get $get): bool => $get('chunking_strategy') === ChunkingStrategy::RECURSIVE_CHARACTER->value),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(VectorIndexTable::columns())
            ->filters(VectorIndexTable::filters())
            ->defaultSort('name')
            ->emptyStateHeading('No vector indexes')
            ->emptyStateDescription('Create an index to configure drivers, embedding providers, and chunking strategies.')
            ->emptyStateIcon(Heroicon::OutlinedRectangleStack)
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create index')
                    ->url(fn (): string => static::getUrl('create'))
                    ->icon(Heroicon::OutlinedPlus)
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVectorIndexes::route('/'),
            'create' => Pages\CreateVectorIndex::route('/create'),
            'edit' => Pages\EditVectorIndex::route('/{record}/edit'),
        ];
    }
}
