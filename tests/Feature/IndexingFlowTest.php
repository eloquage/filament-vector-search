<?php

use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;

it('indexes a document through the processing pipeline', function () {
    $index = VectorIndex::factory()->create([
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);
    $document = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'content' => 'Laravel makes vector search approachable.',
        'status' => DocumentStatus::DRAFT,
    ]);

    ProcessVectorDocument::dispatchSync($document);

    $document->refresh();

    expect($document->status)->toBe(DocumentStatus::COMPLETED);
    expect($document->chunks)->toHaveCount(1);
    expect($document->chunks->first()->embedding)->not->toBeNull();
});
