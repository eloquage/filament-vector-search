# Filament Vector Search

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eloquage/filament-vector-search.svg?style=flat-square)](https://packagist.org/packages/eloquage/filament-vector-search)
[![GitHub Tests Action Status](https://github.com/eloquage/filament-vector-search/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/filament-vector-search/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/eloquage/filament-vector-search.svg?style=flat-square)](https://packagist.org/packages/eloquage/filament-vector-search)

Filament v5 admin plugin for [eloquage/laravel-vector-search](https://github.com/eloquage/laravel-vector-search) with CRUD resources and a semantic search dashboard.

## Requirements

| Requirement  | Version / notes                                                               |
| ------------ | ----------------------------------------------------------------------------- |
| PHP          | 8.4+                                                                          |
| Filament     | 5.x                                                                           |
| Core package | `eloquage/laravel-vector-search`                                              |
| Queue worker | Required for document indexing (`ProcessVectorDocument`, `EmbedVectorChunks`) |
| Database     | PostgreSQL with pgvector, or Meilisearch, depending on driver                 |

## Architecture

```
VectorIndex → VectorDocument → VectorChunk → embedding provider → vector driver
```

The plugin surfaces this pipeline in Filament:

| Artifact          | Class                                               | Purpose                                       |
| ----------------- | --------------------------------------------------- | --------------------------------------------- |
| Dashboard         | `VectorSearchDashboard`                             | Semantic search playground + pipeline widgets |
| Index resource    | `VectorIndexResource`                               | CRUD for vector indexes                       |
| Document resource | `VectorDocumentResource`                            | CRUD for documents + index action             |
| Playground        | `SearchPlayground`                                  | Livewire semantic search UI                   |
| Widgets           | `VectorEngineStatsWidget`, `IndexingPipelineWidget` | Counts and pipeline status                    |

## Installation

### 1. Install the core package first

```bash
composer require eloquage/laravel-vector-search
```

Publish and migrate:

```bash
php artisan vendor:publish --tag=laravel-vector-search-migrations
php artisan migrate
```

Optionally publish config:

```bash
php artisan vendor:publish --tag=laravel-vector-search-config
```

Configure `.env` for your driver and embedding provider. Examples:

**pgvector + OpenAI**

```dotenv
VECTOR_SEARCH_DRIVER=postgres_vector
VECTOR_SEARCH_EMBEDDING_PROVIDER=openai
OPENAI_API_KEY=sk-...
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
```

**pgvector + Ollama (local)**

```dotenv
VECTOR_SEARCH_DRIVER=postgres_vector
VECTOR_SEARCH_EMBEDDING_PROVIDER=ollama
OLLAMA_HOST=http://localhost:11434
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
```

**Meilisearch**

```dotenv
VECTOR_SEARCH_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=masterKey
```

Start a queue worker — indexing is asynchronous:

```bash
php artisan queue:work
```

`ProcessVectorDocument` runs on the queue with a 300-second timeout, uniqueness per document, and backoff of 10, 60, and 300 seconds.

For pgvector, optionally publish and run the HNSW index migration stub from the core package.

### 2. Install the Filament plugin

```bash
composer require eloquage/filament-vector-search
```

Register on your panel:

```php
use Eloquage\FilamentVectorSearch\VectorSearchPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            VectorSearchPlugin::make()
                ->navigationGroup('Knowledge Base')
        );
}
```

The service provider registers the `filament-vector-search.search-playground` Livewire alias automatically.

## Quick start

1. Create a **Vector Index** in the admin (driver, embedding provider, chunking strategy).
2. Create a **Vector Document** with title and content.
3. Click **Index document** on draft records (queues chunking + embedding).
4. Ensure a queue worker is running.
5. Open **Vector Search** (`/admin/vector-search`) and run a semantic query.

## Plugin registration

`VectorSearchPlugin` registers:

- Page: `VectorSearchDashboard` at slug `vector-search` (nav label: "Vector Search")
- Resources: `VectorIndexResource`, `VectorDocumentResource`
- Widgets: `VectorEngineStatsWidget`, `IndexingPipelineWidget`

Default navigation group: **Vector Search**.

### Fluent customization

```php
VectorSearchPlugin::make()
    ->navigationGroup('Knowledge Base')
    ->dashboardPage(CustomVectorSearchDashboard::class)
    ->indexResource(AppVectorIndexResource::class)
    ->documentResource(AppVectorDocumentResource::class)
    ->widgets([
        VectorEngineStatsWidget::class,
        IndexingPipelineWidget::class,
    ]);
```

| Method               | Purpose                                       |
| -------------------- | --------------------------------------------- |
| `navigationGroup()`  | Sets nav group for all plugin pages/resources |
| `dashboardPage()`    | Replace the dashboard page class              |
| `indexResource()`    | Replace the index resource class              |
| `documentResource()` | Replace the document resource class           |
| `widgets()`          | Replace dashboard header widgets              |

## Dashboard and semantic search playground

`VectorSearchDashboard` shows:

- Header widgets: index/document/chunk counts and queue size (database driver), plus indexing pipeline status counts
- `SearchPlayground` embedded via `Filament\Schemas\Components\Livewire`
- Recent failures: latest three failed documents with truncated error logs

`SearchPlayground` behavior:

- Select a vector index and type a semantic query
- Debounced live search (`wire:model.live.debounce.500ms`) after 3+ characters
- Configurable result limit (default 5, max 20 in UI)
- Uses `LaravelVectorSearch::forIndex($index)->search($query, $limit)`

![Vector Search dashboard with semantic query and result cards](docs/images/01-dashboard-playground.png)

![Indexing pipeline widget showing document status counts](docs/images/06-pipeline-widget.png)

## Resources

### VectorIndexResource

Fields: name, driver, embedding provider, embedding model, dimensions (pgvector only), chunking strategy, chunk size, chunk overlap (recursive strategy only).

Includes a documents relation manager.

![Vector indexes list with driver and embedding badges](docs/images/02-indexes-list.png)

![Create vector index form](docs/images/03-create-index.png)

### VectorDocumentResource

Fields: index, title, content (textarea).

Table action **Index document** appears for `draft` records and dispatches `ProcessVectorDocument`.

Includes a chunks relation manager (paginated 10/25/50).

![Vector documents list with status badges](docs/images/04-documents-list.png)

![Document edit page with chunks relation manager](docs/images/05-document-chunks.png)

## Customization

### Navigation icons

Subclass a page or resource and override `$navigationIcon` using the Filament 5 union type (`string|BackedEnum|null`):

```php
use Filament\Support\Icons\Heroicon;

class CustomVectorSearchDashboard extends VectorSearchDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Knowledge Base';
    }
}
```

Register the subclass via `->dashboardPage(CustomVectorSearchDashboard::class)`.

### Policies

The plugin does not ship policies. Register app-level policies on the core models:

```php
use Eloquage\LaravelVectorSearch\Models\VectorDocument;
use Eloquage\LaravelVectorSearch\Models\VectorIndex;

Gate::policy(VectorIndex::class, VectorIndexPolicy::class);
Gate::policy(VectorDocument::class, VectorDocumentPolicy::class);
```

Filament respects `viewAny`, `view`, `create`, `update`, and `delete` automatically.

Gate the index action in an extended resource:

```php
Action::make('indexDocument')
    ->visible(fn (VectorDocument $record): bool => auth()->user()->can('update', $record))
```

See `workbench/app/Policies/` for example policies used in package tests.

### Extending resources

```php
class AppVectorDocumentResource extends VectorDocumentResource
{
    public static function form(Schema $schema): Schema
    {
        return parent::form($schema);
    }
}
```

Register with `->documentResource(AppVectorDocumentResource::class)`.

To auto-index on create, extend `CreateVectorDocument` and dispatch `ProcessVectorDocument` in `afterCreate()`.

### Custom playground

Extend `SearchPlayground`, override `search()` or swap the view, then embed your component in a custom dashboard page registered via `->dashboardPage()`.

## Troubleshooting

See [docs/troubleshooting.md](docs/troubleshooting.md) for Filament 5 namespace pitfalls, Livewire `wire:model` issues, and large-document performance guidance.

## Testing

```bash
composer test
composer analyse
composer format
```

## Related packages

- [eloquage/laravel-vector-search](https://github.com/eloquage/laravel-vector-search) — core (required)
- [eloquage/laravel-vector-search-ai](https://github.com/eloquage/laravel-vector-search-ai) — Laravel AI SDK + MCP (optional)

## Changelog

See [CHANGELOG](CHANGELOG.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
