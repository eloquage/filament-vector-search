<?php

use Eloquage\FilamentVectorSearch\Pages\VectorSearchDashboard;
use Eloquage\FilamentVectorSearch\Widgets\IndexingPipelineWidget;
use Eloquage\FilamentVectorSearch\Widgets\VectorEngineStatsWidget;
use Eloquage\LaravelVectorSearch\Enums\DocumentStatus;
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

use function Pest\Livewire\livewire;

it('registers header widgets on the dashboard', function () {
    $reflection = new ReflectionClass(VectorSearchDashboard::class);
    $method = $reflection->getMethod('getHeaderWidgets');
    $method->setAccessible(true);

    expect($method->invoke(new VectorSearchDashboard))->toBe([
        VectorEngineStatsWidget::class,
        IndexingPipelineWidget::class,
    ]);
});

it('uses the vector search dashboard slug', function () {
    expect(VectorSearchDashboard::getSlug())->toBe('vector-search');
});

it('shows recent failures on the dashboard for admin users', function () {
    $this->actingAsAdmin();

    $index = VectorIndex::factory()->create();
    VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'title' => 'Broken doc',
        'status' => DocumentStatus::FAILED,
        'error_log' => 'Embedding provider unavailable',
    ]);

    livewire(VectorSearchDashboard::class)
        ->assertSuccessful()
        ->assertSee('Broken doc');
});

it('reports queue size from the jobs table in the stats widget', function () {
    if (! Schema::hasTable('jobs')) {
        Schema::create('jobs', function ($table) {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    config(['queue.default' => 'database']);

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => time(),
        'created_at' => time(),
    ]);

    Livewire::test(VectorEngineStatsWidget::class)
        ->assertSee('1');
});

it('aggregates pipeline status counts in a single query group', function () {
    $index = VectorIndex::factory()->create();

    VectorDocument::factory()->for($index, 'vectorIndex')->count(2)->create([
        'status' => DocumentStatus::DRAFT,
    ]);
    VectorDocument::factory()->for($index, 'vectorIndex')->create([
        'status' => DocumentStatus::FAILED,
    ]);

    Livewire::test(IndexingPipelineWidget::class)
        ->assertSee('Draft')
        ->assertSee('2')
        ->assertSee('Failed')
        ->assertSee('1');
});
