<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Policies\VectorDocumentPolicy;
use Workbench\App\Policies\VectorIndexPolicy;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(VectorIndex::class, VectorIndexPolicy::class);
        Gate::policy(VectorDocument::class, VectorDocumentPolicy::class);
    }
}
