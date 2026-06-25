<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources;

use BackedEnum;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\RelationManagers\DocumentsRelationManager;
use Eloquage\LaravelVectorSearch\Contracts\HasLabel;
use Eloquage\LaravelVectorSearch\Enums\ChunkingStrategy;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Enums\IndexDriver;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VectorIndexResource extends Resource
{
    protected static ?string $model = VectorIndex::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Vector Search';

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
                            ->options(self::enumOptions(IndexDriver::cases()))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if ($state === IndexDriver::MEILISEARCH->value) {
                                    $set('dimensions', null);
                                }
                            }),
                        Select::make('embedding_provider')
                            ->options(self::enumOptions(EmbeddingProvider::cases()))
                            ->required()
                            ->live(),
                        TextInput::make('embedding_model')
                            ->required()
                            ->placeholder('e.g. text-embedding-3-small'),
                        TextInput::make('dimensions')
                            ->numeric()
                            ->visible(fn (Get $get): bool => $get('driver') === IndexDriver::POSTGRES_VECTOR->value),
                        Select::make('chunking_strategy')
                            ->options(self::enumOptions(ChunkingStrategy::cases()))
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
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('driver')->badge(),
                TextColumn::make('embedding_provider')->badge(),
                TextColumn::make('embedding_model'),
                TextColumn::make('documents_count')->counts('documents')->label('Documents'),
            ])
            ->defaultSort('name');
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

    /**
     * @param  array<BackedEnum&HasLabel>  $cases
     * @return array<string, string>
     */
    protected static function enumOptions(array $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $options[$case->value] = $case->getLabel() ?? $case->value;
        }

        return $options;
    }
}
