<?php

use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages\ListVectorDocuments;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Queue;

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

it('filters vector documents by status', function () {
    $this->actingAsAdmin();

    $index = VectorIndex::factory()->create();
    $draft = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::DRAFT,
    ]);
    $completed = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::COMPLETED,
    ]);

    livewire(ListVectorDocuments::class)
        ->assertCanSeeTableRecords([$draft, $completed])
        ->filterTable('status', DocumentStatus::DRAFT->value)
        ->assertCanSeeTableRecords([$draft])
        ->assertCanNotSeeTableRecords([$completed]);
});

it('shows the index document action only for draft records', function () {
    $this->actingAsAdmin();

    $index = VectorIndex::factory()->create();
    $draft = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::DRAFT,
    ]);
    $completed = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::COMPLETED,
    ]);

    livewire(ListVectorDocuments::class)
        ->assertActionVisible(TestAction::make('indexDocument')->table($draft))
        ->assertActionHidden(TestAction::make('indexDocument')->table($completed));
});

it('dispatches indexing when the table action is triggered', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $index = VectorIndex::factory()->create();
    $document = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::DRAFT,
    ]);

    livewire(ListVectorDocuments::class)
        ->callAction(TestAction::make('indexDocument')->table($document));

    Queue::assertPushed(ProcessVectorDocument::class, fn (ProcessVectorDocument $job): bool => $job->document->is($document));
});

it('exposes the vector document model on the resource', function () {
    expect(VectorDocumentResource::getModel())->toBe(VectorDocument::class);
});
