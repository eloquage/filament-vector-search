<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Support;

class ArchiveIntegration
{
    public static function isEnabled(): bool
    {
        return (bool) config('filament-vector-search.archive.enabled', true);
    }

    public static function isAvailable(): bool
    {
        return self::isEnabled() && interface_exists('Eloquage\\LaravelDocumentArchive\\Contracts\\ManifestService');
    }

    public static function isCorrelationAvailable(): bool
    {
        return self::isEnabled() && class_exists('Eloquage\\LaravelDocumentArchiveAi\\Ai\\Agents\\CorrelationAgent');
    }

    public static function defaultIndexName(): ?string
    {
        if (! self::isEnabled()) {
            return null;
        }

        $keys = config('filament-vector-search.archive.default_index_config_keys', []);

        if (! is_array($keys)) {
            return null;
        }

        foreach ($keys as $key) {
            if (! is_string($key) || $key === 'filament-vector-search.default_playground_index') {
                continue;
            }

            $value = config($key);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return 'document-archive';
    }

    /**
     * @return array<string, mixed>
     */
    public static function metadataForRecord(object $record): array
    {
        $metadata = $record->getAttribute('metadata');

        return is_array($metadata) ? $metadata : [];
    }

    public static function hasArchiveMetadata(object $record): bool
    {
        $metadata = self::metadataForRecord($record);

        return filled($metadata['collection'] ?? null)
            && filled($metadata['canonical_basename'] ?? null);
    }

    public static function collectionFromRecord(object $record): ?string
    {
        $metadata = self::metadataForRecord($record);
        $collection = $metadata['collection'] ?? null;

        return is_string($collection) && $collection !== '' ? $collection : null;
    }

    public static function canonicalBasenameFromRecord(object $record): ?string
    {
        $metadata = self::metadataForRecord($record);
        $basename = $metadata['canonical_basename'] ?? null;

        return is_string($basename) && $basename !== '' ? $basename : null;
    }
}
