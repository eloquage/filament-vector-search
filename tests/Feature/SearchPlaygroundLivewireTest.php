<?php

use Eloquage\FilamentVectorSearch\Livewire\SearchPlayground;
use Eloquage\FilamentVectorSearch\Support\DefaultPlaygroundIndexResolver;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Managers\EmbeddingProviderManager;
use Eloquage\LaravelVectorSearch\Models\VectorChunk;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Livewire\Livewire;

it('mounts the search playground with a default index', function () {
    $index = VectorIndex::factory()->create([
        'name' => 'default-index',
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);

    Livewire::test(SearchPlayground::class)
        ->assertSet('indexId', (string) $index->id)
        ->assertSet('results', collect());
});

it('does not search until the query has at least three characters via livewire', function () {
    $index = VectorIndex::factory()->create([
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);

    Livewire::test(SearchPlayground::class)
        ->set('indexId', (string) $index->id)
        ->set('query', 'ab')
        ->assertCount('results', 0);
});

it('resolves the preferred playground index from config', function () {
    config([
        'filament-vector-search.default_playground_index' => 'preferred',
        'filament-vector-search.archive.enabled' => false,
    ]);

    $preferred = VectorIndex::factory()->create(['name' => 'preferred']);
    VectorIndex::factory()->create(['name' => 'other']);

    $resolver = app(DefaultPlaygroundIndexResolver::class);

    expect($resolver->resolve())->toBe((string) $preferred->id);
});

it('eager loads vector documents on search results', function () {
    $index = VectorIndex::factory()->create([
        'name' => 'eager',
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);
    $document = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'title' => 'Eager title',
        'status' => DocumentStatus::COMPLETED,
    ]);

    $embedding = app(EmbeddingProviderManager::class)
        ->driver(EmbeddingProvider::NULL_PROVIDER->value)
        ->embed('eager loading test');

    VectorChunk::factory()->for($document, 'vectorDocument')->create([
        'chunk_index' => 0,
        'content' => 'Eager loading test content',
        'embedding' => $embedding,
    ]);

    $component = Livewire::test(SearchPlayground::class)
        ->set('indexId', (string) $index->id)
        ->set('query', 'eager')
        ->call('search');

    expect($component->get('results')->first()->relationLoaded('vectorDocument'))->toBeTrue();
});
