<?php

use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages\ListVectorIndexes;
use Eloquage\LaravelVectorSearch\Enums\ChunkingStrategy;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Enums\IndexDriver;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

it('persists postgres vector indexes with dimensions', function () {
    VectorIndex::query()->create([
        'name' => 'docs',
        'driver' => IndexDriver::POSTGRES_VECTOR,
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'embedding_model' => 'null',
        'dimensions' => 8,
        'chunking_strategy' => ChunkingStrategy::RECURSIVE_CHARACTER,
        'chunk_size' => 1000,
        'chunk_overlap' => 200,
    ]);

    assertDatabaseHas(VectorIndex::class, [
        'name' => 'docs',
        'driver' => IndexDriver::POSTGRES_VECTOR->value,
        'dimensions' => 8,
    ]);
});

it('only shows dimensions for the postgres vector driver in the form', function () {
    $visible = fn (string $driver): bool => $driver === IndexDriver::POSTGRES_VECTOR->value;

    expect($visible(IndexDriver::POSTGRES_VECTOR->value))->toBeTrue()
        ->and($visible(IndexDriver::MEILISEARCH->value))->toBeFalse();
});

it('exposes the vector index model on the resource', function () {
    expect(VectorIndexResource::getModel())->toBe(VectorIndex::class);
});

it('lists vector indexes in the database', function () {
    $indexes = VectorIndex::factory()->count(2)->create();

    expect(VectorIndex::query()->whereKey($indexes->pluck('id'))->count())->toBe(2);
});

it('filters vector indexes by driver', function () {
    $this->actingAsAdmin();

    $postgres = VectorIndex::factory()->create([
        'driver' => IndexDriver::POSTGRES_VECTOR,
    ]);
    $meilisearch = VectorIndex::factory()->create([
        'driver' => IndexDriver::MEILISEARCH,
    ]);

    livewire(ListVectorIndexes::class)
        ->assertCanSeeTableRecords([$postgres, $meilisearch])
        ->filterTable('driver', IndexDriver::POSTGRES_VECTOR->value)
        ->assertCanSeeTableRecords([$postgres])
        ->assertCanNotSeeTableRecords([$meilisearch]);
});
