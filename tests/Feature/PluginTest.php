<?php

use Eloquage\FilamentVectorSearch\VectorSearchPlugin;

it('registers the vector search plugin', function () {
    expect(VectorSearchPlugin::make())
        ->toBeInstanceOf(VectorSearchPlugin::class)
        ->and(VectorSearchPlugin::make()->getId())
        ->toBe('filament-vector-search');
});
