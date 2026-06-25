<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Actions;

use Eloquage\FilamentVectorSearch\Support\ArchiveIntegration;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class OpenManifestAction
{
    public static function make(): Action
    {
        return Action::make('openManifest')
            ->label('View manifest')
            ->icon('heroicon-o-folder-open')
            ->visible(fn (VectorDocument $record): bool => ArchiveIntegration::isAvailable()
                && ArchiveIntegration::hasArchiveMetadata($record))
            ->modalHeading('Manifest entry')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(function (VectorDocument $record): HtmlString {
                $collection = ArchiveIntegration::collectionFromRecord($record);
                $basename = ArchiveIntegration::canonicalBasenameFromRecord($record);

                if ($collection === null || $basename === null) {
                    return new HtmlString('<p class="text-sm text-gray-500">No archive metadata on this document.</p>');
                }

                $service = app('Eloquage\\LaravelDocumentArchive\\Contracts\\ManifestService');
                $entry = $service->lookup($collection, canonicalBasename: $basename);
                $entry = is_array($entry) ? $entry : [];

                $lines = [
                    'Collection' => $collection,
                    'Basename' => $basename,
                    'Source file' => (string) ($entry['source_file'] ?? ArchiveIntegration::metadataForRecord($record)['source_file'] ?? '—'),
                    'Status' => (string) ($entry['status'] ?? ArchiveIntegration::metadataForRecord($record)['status'] ?? '—'),
                ];

                $html = '<dl class="space-y-2 text-sm">';

                foreach ($lines as $label => $value) {
                    $html .= '<div><dt class="font-medium text-gray-700 dark:text-gray-200">'.e($label).'</dt>';
                    $html .= '<dd class="text-gray-600 dark:text-gray-300">'.e($value).'</dd></div>';
                }

                $html .= '</dl>';

                return new HtmlString($html);
            });
    }
}
