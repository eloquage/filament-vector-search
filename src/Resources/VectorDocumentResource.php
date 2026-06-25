<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Resources;

use BackedEnum;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\RelationManagers\ChunksRelationManager;
use Eloquage\FilamentVectorSearch\Support\VectorSearchNavigation;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class VectorDocumentResource extends Resource
{
    protected static ?string $model = VectorDocument::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

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
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('vectorIndex.name')->label('Index')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('chunks_count')->counts('chunks')->label('Chunks'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                Action::make('indexDocument')
                    ->label('Index document')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (VectorDocument $record): bool => $record->status === DocumentStatus::DRAFT)
                    ->action(fn (VectorDocument $record) => ProcessVectorDocument::dispatch($record)),
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
