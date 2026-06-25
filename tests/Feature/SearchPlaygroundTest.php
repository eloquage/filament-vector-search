<?php

use Eloquage\FilamentVectorSearch\Livewire\SearchPlayground;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Facades\LaravelVectorSearch;
use Eloquage\LaravelVectorSearch\Managers\EmbeddingProviderManager;
use Eloquage\LaravelVectorSearch\Models\VectorChunk;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;

it('clears results when search is called with a blank query', function () {
    $index = VectorIndex::factory()->create([
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);

    $playground = new SearchPlayground;
    $playground->results = collect();
    $playground->indexId = $index->id;
    $playground->query = 'hello';
    $playground->search();
    $playground->query = '';
    $playground->search();

    expect($playground->results)->toBeEmpty();
});

it('searches when the query has at least three characters', function () {
    $index = VectorIndex::factory()->create([
        'name' => 'playground',
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);
    $document = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::COMPLETED,
    ]);
    $embedding = app(EmbeddingProviderManager::class)
        ->forIndex($index)
        ->embed('deployment guide');

    VectorChunk::factory()->for($document, 'vectorDocument')->create([
        'chunk_index' => 0,
        'content' => 'Deploy with artisan migrate and queue workers.',
        'embedding' => $embedding,
    ]);

    $playground = new SearchPlayground;
    $playground->results = collect();
    $playground->indexId = $index->id;
    $playground->query = 'de';
    $playground->updatedQuery();
    expect($playground->results)->toBeEmpty();

    $playground->query = 'deploy';
    $playground->updatedQuery();
    expect($playground->results)->toHaveCount(1);
});

it('re-runs search when the index changes', function () {
    $firstIndex = VectorIndex::factory()->create([
        'name' => 'first',
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);
    $secondIndex = VectorIndex::factory()->create([
        'name' => 'second',
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);

    $document = VectorDocument::factory()->for($secondIndex, 'vectorIndex')->create([
        'status' => DocumentStatus::COMPLETED,
    ]);
    $embedding = app(EmbeddingProviderManager::class)
        ->forIndex($secondIndex)
        ->embed('kubernetes');

    VectorChunk::factory()->for($document, 'vectorDocument')->create([
        'chunk_index' => 0,
        'content' => 'Kubernetes scales container workloads.',
        'embedding' => $embedding,
    ]);

    $playground = new SearchPlayground;
    $playground->indexId = $firstIndex->id;
    $playground->query = 'kubernetes';
    $playground->search();
    expect($playground->results)->toBeEmpty();

    $playground->indexId = $secondIndex->id;
    $playground->updatedIndexId();
    expect($playground->results)->toHaveCount(1);
});

it('respects the result limit', function () {
    $index = VectorIndex::factory()->create([
        'name' => 'limits',
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);
    $document = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::COMPLETED,
    ]);
    $embedding = app(EmbeddingProviderManager::class)
        ->forIndex($index)
        ->embed('laravel');

    foreach (range(0, 4) as $chunkIndex) {
        VectorChunk::factory()->for($document, 'vectorDocument')->create([
            'chunk_index' => $chunkIndex,
            'content' => "Laravel chunk {$chunkIndex}",
            'embedding' => $embedding,
        ]);
    }

    $playground = new SearchPlayground;
    $playground->results = collect();
    $playground->indexId = $index->id;
    $playground->limit = 2;
    $playground->query = 'laravel';
    $playground->search();

    expect($playground->results)->toHaveCount(2);
    expect(LaravelVectorSearch::forIndex('limits')->search('laravel', 2))->toHaveCount(2);
});
