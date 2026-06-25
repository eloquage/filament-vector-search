# Filament Vector Search Plugin

Filament v5 plugin for `eloquage/laravel-vector-search`.

## Registration

```php
->plugin(
    \Eloquage\FilamentVectorSearch\VectorSearchPlugin::make()
        ->navigationGroup('Knowledge Base')
)
```

## Registered artifacts

| Type | Class |
|------|-------|
| Dashboard | `VectorSearchDashboard` (`/vector-search`) |
| Index resource | `VectorIndexResource` |
| Document resource | `VectorDocumentResource` |
| Playground | `SearchPlayground` (Livewire) |
| Widgets | `VectorEngineStatsWidget`, `IndexingPipelineWidget` |

## Filament 5 namespaces

| Use | Namespace |
|-----|-----------|
| Form fields | `Filament\Forms\Components\` |
| Layout / schema | `Filament\Schemas\Components\` |
| Utilities | `Filament\Schemas\Components\Utilities\` (`Get`, `Set`) |
| Table columns | `Filament\Tables\Columns\` |
| Actions | `Filament\Actions\` |

## SearchPlayground

- Debounced live search at 3+ characters (`wire:model.live.debounce.500ms`)
- `LaravelVectorSearch::forIndex($index)->search($query, $limit)`
- Livewire alias: `filament-vector-search.search-playground`

## Customization

Fluent API on `VectorSearchPlugin`: `navigationGroup()`, `dashboardPage()`, `indexResource()`, `documentResource()`, `widgets()`.

Shared table builders: `VectorIndexTable`, `VectorDocumentTable`, and `VectorDocumentTableConfiguration` centralize filters, columns, and the index action.

Abstract policies: extend `VectorIndexPolicy` and `VectorDocumentPolicy`, implement `canManage()`, and register on the core models.

Subclass pages/resources for icons (`Heroicon` enum) and extra fields. Register policies on `VectorIndex` / `VectorDocument` in the host app.

## Prerequisites

Install and migrate `eloquage/laravel-vector-search` and run a queue worker before using the Filament plugin.
