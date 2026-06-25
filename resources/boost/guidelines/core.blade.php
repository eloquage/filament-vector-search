# Filament Vector Search Plugin

Filament v5 admin plugin for `eloquage/laravel-vector-search`.

## Registration

```php
->plugin(
    \Eloquage\FilamentVectorSearch\VectorSearchPlugin::make()
        ->navigationGroup('Knowledge Base')
)
```

## Artifacts

| Type | Class |
|------|-------|
| Dashboard | `VectorSearchDashboard` (`/vector-search`) |
| Index resource | `VectorIndexResource` |
| Document resource | `VectorDocumentResource` |
| Playground | `SearchPlayground` (Livewire) |
| Widgets | `VectorEngineStatsWidget`, `IndexingPipelineWidget` |

## Config

Publish: `php artisan vendor:publish --tag=filament-vector-search-config`

| Key | Purpose |
|-----|---------|
| `default_playground_index` | Preferred playground index name |
| `archive.enabled` | Soft coupling to document-archive metadata/actions |
| `widgets.polling_interval` | Stats widget refresh interval |

## Document archive bridge

Optional UI when `ManifestService` / `CorrelationAgent` are available:

- `ArchiveIntegration` — feature detection without hard dependencies
- `OpenManifestAction` — manifest lookup modal
- `CorrelateDocumentAction` — `CorrelationAgent` modal on list/header

## Shared table builders

- `VectorIndexTable` — index columns and filters
- `VectorDocumentTable` — document columns, filters, bulk index/re-index actions
- `VectorDocumentTableConfiguration` — resource vs relation manager column sets

## Policies

Extend `VectorIndexPolicy` / `VectorDocumentPolicy` and register on core models. Dashboard access uses `viewAny` on `VectorIndex` or `VectorDocument`.

## Filament 5 namespaces

| Use | Namespace |
|-----|-----------|
| Form fields | `Filament\Forms\Components\` |
| Layout / schema | `Filament\Schemas\Components\` |
| Table columns | `Filament\Tables\Columns\` |
| Actions | `Filament\Actions\` |
