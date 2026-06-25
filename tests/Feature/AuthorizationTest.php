<?php

use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages\ListVectorDocuments;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages\ListVectorIndexes;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Workbench\App\Models\User;

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
