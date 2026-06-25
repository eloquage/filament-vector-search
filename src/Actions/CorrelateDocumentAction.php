<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Actions;

use Eloquage\FilamentVectorSearch\Support\ArchiveIntegration;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class CorrelateDocumentAction
{
    public static function makeRecordAction(): Action
    {
        return self::baseAction('correlateDocument')
            ->label('Correlate')
            ->visible(fn (VectorDocument $record): bool => ArchiveIntegration::isCorrelationAvailable()
                && ArchiveIntegration::hasArchiveMetadata($record))
            ->fillForm(fn (VectorDocument $record): array => [
                'collection' => ArchiveIntegration::collectionFromRecord($record) ?? '',
                'query' => '',
            ]);
    }

    public static function makeHeaderAction(): Action
    {
        return self::baseAction('correlateCollection')
            ->label('Correlate collection')
            ->visible(fn (): bool => ArchiveIntegration::isCorrelationAvailable());
    }

    private static function baseAction(string $name): Action
    {
        return Action::make($name)
            ->icon('heroicon-o-sparkles')
            ->schema([
                TextInput::make('collection')
                    ->label('Collection')
                    ->required()
                    ->maxLength(255),
                TextInput::make('query')
                    ->label('Correlation query')
                    ->required()
                    ->maxLength(2000),
            ])
            ->action(function (array $data, ?VectorDocument $record = null): void {
                $collection = self::resolveCollection($data, $record);
                $query = (string) ($data['query'] ?? '');

                if ($collection === null || $query === '') {
                    Notification::make()
                        ->title('Collection and query are required')
                        ->danger()
                        ->send();

                    return;
                }

                $agentClass = 'Eloquage\\LaravelDocumentArchiveAi\\Ai\\Agents\\CorrelationAgent';

                /** @var object{correlate: callable, text?: string} $agent */
                $agent = app($agentClass, [
                    'collection' => $collection,
                    'query' => $query,
                ]);

                $response = $agent->correlate();

                Notification::make()
                    ->title('Correlation complete')
                    ->body(str((string) $response->text)->limit(2000)->toString())
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolveCollection(array $data, ?VectorDocument $record): ?string
    {
        if ($record !== null) {
            $fromRecord = ArchiveIntegration::collectionFromRecord($record);

            if ($fromRecord !== null) {
                return $fromRecord;
            }
        }

        $collection = $data['collection'] ?? null;

        return is_string($collection) && $collection !== '' ? $collection : null;
    }
}
