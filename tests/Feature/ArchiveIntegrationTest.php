<?php

use Eloquage\FilamentVectorSearch\Support\ArchiveIntegration;
use Eloquage\FilamentVectorSearch\Tables\VectorDocumentTableConfiguration;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;

it('disables archive integration when configured off', function () {
    config(['filament-vector-search.archive.enabled' => false]);

    expect(ArchiveIntegration::isAvailable())->toBeFalse()
        ->and(ArchiveIntegration::isCorrelationAvailable())->toBeFalse();
});

it('does not include archive columns when integration is unavailable', function () {
    config(['filament-vector-search.archive.enabled' => false]);

    $configuration = VectorDocumentTableConfiguration::forResource();

    expect($configuration->includeArchiveColumns)->toBeFalse();
});

it('reads archive metadata from vector documents', function () {
    $document = new VectorDocument([
        'metadata' => [
            'collection' => '479-26-0T9MTS',
            'canonical_basename' => 'scan.pdf',
        ],
    ]);

    expect(ArchiveIntegration::hasArchiveMetadata($document))->toBeTrue()
        ->and(ArchiveIntegration::collectionFromRecord($document))->toBe('479-26-0T9MTS')
        ->and(ArchiveIntegration::canonicalBasenameFromRecord($document))->toBe('scan.pdf');
});

it('resolves the default archive index name from config keys', function () {
    config([
        'filament-vector-search.archive.enabled' => true,
        'document-archive.vector_index' => 'document-archive',
    ]);

    expect(ArchiveIntegration::defaultIndexName())->toBe('document-archive');
});
