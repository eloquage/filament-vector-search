<?php

use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages\ListVectorDocuments;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

it('creates vector documents in the database', function () {
    $index = VectorIndex::factory()->create([
        'embedding_provider' => EmbeddingProvider::NULL_PROVIDER,
        'dimensions' => 8,
        'embedding_model' => 'null',
    ]);

    VectorDocument::query()->create([
        'vector_index_id' => $index->id,
        'title' => 'Deployment guide',
        'content' => 'Run migrations and queue workers before searching.',
        'status' => DocumentStatus::DRAFT,
    ]);

    assertDatabaseHas(VectorDocument::class, [
        'title' => 'Deployment guide',
        'vector_index_id' => $index->id,
        'status' => DocumentStatus::DRAFT->value,
    ]);
});

it('shows the index document action only for draft records', function () {
    $index = VectorIndex::factory()->create();
    $draft = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::DRAFT,
    ]);
    $completed = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::COMPLETED,
    ]);

    $visible = fn (VectorDocument $record): bool => $record->status === DocumentStatus::DRAFT;

    expect($visible($draft))->toBeTrue()
        ->and($visible($completed))->toBeFalse();
});

it('dispatches indexing when the table action is triggered', function () {
    Queue::fake();

    $index = VectorIndex::factory()->create();
    $document = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::DRAFT,
    ]);

    ProcessVectorDocument::dispatch($document);

    Queue::assertPushed(ProcessVectorDocument::class, fn (ProcessVectorDocument $job): bool => $job->document->is($document));
});

it('exposes the vector document model on the resource', function () {
    expect(VectorDocumentResource::getModel())->toBe(VectorDocument::class);
});

it('denies non-admin users access to vector document resources', function () {
    $user = User::factory()->nonAdmin()->create();
    $this->actingAs($user);

    livewire(ListVectorDocuments::class)
        ->assertForbidden();
});
