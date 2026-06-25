<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources;

use BackedEnum;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\RelationManagers\ChunksRelationManager;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTable;
use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTableConfiguration;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VectorDocumentResource extends Resource
{
    protected static ?string $model = VectorDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return VectorSearchNavigation::group();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->schema([
                        Select::make('vector_index_id')
                            ->label('Index')
                            ->relationship('vectorIndex', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('content')
                            ->required()
                            ->rows(10)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(VectorDocumentTable::columns(VectorDocumentTableConfiguration::forResource()))
            ->filters(VectorDocumentTable::filters())
            ->recordActions([
                VectorDocumentTable::indexDocumentAction(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ChunksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVectorDocuments::route('/'),
            'create' => Pages\CreateVectorDocument::route('/create'),
            'edit' => Pages\EditVectorDocument::route('/{record}/edit'),
        ];
    }
}
