# Changelog

All notable changes to `filament-vector-search` will be documented in this file.

## Unreleased

### Added

- Fluent `VectorSearchPlugin` customization: `navigationGroup()`, `dashboardPage()`, `indexResource()`, `documentResource()`, `widgets()`
- `VectorSearchNavigation` helper for shared navigation group config
- `EnumOptions`, `VectorIndexTable`, `VectorDocumentTable`, and `VectorDocumentTableConfiguration` for shared resource table configuration
- Abstract `VectorIndexPolicy` and `VectorDocumentPolicy` base classes for host-app authorization
- Workbench harness with example policies and Filament panel for package tests
- Comprehensive README, troubleshooting guide, and screenshot placeholders

### Changed

- Resources and relation managers now delegate table columns, filters, and actions to shared table builders
- Navigation icons use Filament `Heroicon` enum values
- Workbench policies extend package base policies

### Documentation

- Expanded installation prerequisites for `eloquage/laravel-vector-search`
- Dashboard, playground, customization, and Filament 5 namespace guidance
- Boost agent guidelines for plugin registration and extension points
- README policies section documents abstract base policies and shared index action authorization
