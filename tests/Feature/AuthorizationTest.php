<?php

use Eloquage\FilamentVectorSearch\Pages\VectorSearchDashboard;
use Eloquage\FilamentVectorSearch\Policies\VectorDocumentPolicy as PackageVectorDocumentPolicy;
use Eloquage\FilamentVectorSearch\Policies\VectorIndexPolicy as PackageVectorIndexPolicy;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages\CreateVectorDocument;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages\ListVectorDocuments;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages\CreateVectorIndex;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages\ListVectorIndexes;
use Eloquage\LaravelVectorSearch\Enums\ChunkingStrategy;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Enums\EmbeddingProvider;
use Eloquage\LaravelVectorSearch\Enums\IndexDriver;
use Eloquage\LaravelVectorSearch\Jobs\ProcessVectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Models\User;
use Workbench\App\Policies\VectorDocumentPolicy;
use Workbench\App\Policies\VectorIndexPolicy;

use function Pest\Livewire\livewire;

it('denies non-admin users access to vector index resources', function () {
    $user = User::factory()->nonAdmin()->create();
    $this->actingAs($user);

    livewire(ListVectorIndexes::class)
        ->assertForbidden();
});

it('denies non-admin users access to vector document resources', function () {
    $user = User::factory()->nonAdmin()->create();
    $this->actingAs($user);

    livewire(ListVectorDocuments::class)
        ->assertForbidden();
});

it('allows admin users to access vector resources through policies', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', VectorIndex::class))->toBeTrue()
        ->and($user->can('viewAny', VectorDocument::class))->toBeTrue();
});

it('extends package base policies in the workbench', function () {
    expect(VectorIndexPolicy::class)
        ->toExtend(PackageVectorIndexPolicy::class)
        ->and(VectorDocumentPolicy::class)
        ->toExtend(PackageVectorDocumentPolicy::class);
});

it('denies non-admin users access to create vector index pages', function () {
    $user = User::factory()->nonAdmin()->create();
    $this->actingAs($user);

    livewire(CreateVectorIndex::class)
        ->assertForbidden();
});

it('denies non-admin users access to create vector document pages', function () {
    $user = User::factory()->nonAdmin()->create();
    $this->actingAs($user);

    livewire(CreateVectorDocument::class)
        ->assertForbidden();
});

it('denies non-admin users access to the vector search dashboard', function () {
    $user = User::factory()->nonAdmin()->create();
    $this->actingAs($user);

    livewire(VectorSearchDashboard::class)
        ->assertForbidden();
});

it('allows admin users to access the vector search dashboard', function () {
    $this->actingAsAdmin();

    livewire(VectorSearchDashboard::class)
        ->assertSuccessful();
});

it('creates vector indexes through the filament form', function () {
    $this->actingAsAdmin();

    livewire(CreateVectorIndex::class)
        ->fillForm([
            'name' => 'admin-index',
            'driver' => IndexDriver::POSTGRES_VECTOR->value,
            'embedding_provider' => EmbeddingProvider::NULL_PROVIDER->value,
            'embedding_model' => 'null',
            'dimensions' => 8,
            'chunking_strategy' => ChunkingStrategy::RECURSIVE_CHARACTER->value,
            'chunk_size' => 1000,
            'chunk_overlap' => 200,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(VectorIndex::query()->where('name', 'admin-index')->exists())->toBeTrue();
});

it('bulk indexes draft documents when authorized', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $index = VectorIndex::factory()->create();
    $drafts = VectorDocument::factory()->for($index, 'vectorIndex')->count(2)->create([
        'status' => DocumentStatus::DRAFT,
    ]);

    livewire(ListVectorDocuments::class)
        ->callTableBulkAction('indexSelected', $drafts);

    Queue::assertPushed(ProcessVectorDocument::class, 2);
});

it('bulk reindexes failed documents when authorized', function () {
    Queue::fake();

    $this->actingAsAdmin();

    $index = VectorIndex::factory()->create();
    $failed = VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::FAILED,
        'error_log' => 'Previous failure',
    ]);

    livewire(ListVectorDocuments::class)
        ->callTableBulkAction('reindexFailed', [$failed]);

    Queue::assertPushed(ProcessVectorDocument::class, fn (ProcessVectorDocument $job): bool => $job->document->is($failed));

    expect($failed->fresh()->error_log)->toBeNull();
});
