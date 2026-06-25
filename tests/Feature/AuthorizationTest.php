<?php

use Eloquage\FilamentVectorSearch\Policies\VectorDocumentPolicy as PackageVectorDocumentPolicy;
use Eloquage\FilamentVectorSearch\Policies\VectorIndexPolicy as PackageVectorIndexPolicy;
use Eloquage\FilamentVectorSearch\Resources\VectorDocumentResource\Pages\ListVectorDocuments;
use Eloquage\FilamentVectorSearch\Resources\VectorIndexResource\Pages\ListVectorIndexes;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
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
