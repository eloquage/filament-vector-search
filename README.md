# Filament Vector Search

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eloquage/filament-vector-search.svg?style=flat-square)](https://packagist.org/packages/eloquage/filament-vector-search)
[![GitHub Tests Action Status](https://github.com/eloquage/filament-vector-search/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/filament-vector-search/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/eloquage/filament-vector-search.svg?style=flat-square)](https://packagist.org/packages/eloquage/filament-vector-search)

Filament v5 admin plugin for [eloquage/laravel-vector-search](https://github.com/eloquage/laravel-vector-search) with CRUD resources and a semantic search dashboard.

## Requirements

- PHP 8.4+
- Filament 5.x
- `eloquage/laravel-vector-search`

## Installation

```bash
composer require eloquage/laravel-vector-search
composer require eloquage/filament-vector-search
```

Publish and migrate the core package first:

```bash
php artisan vendor:publish --tag=laravel-vector-search-migrations
php artisan migrate
```

Register the plugin on your Filament panel:

```php
use Eloquage\FilamentVectorSearch\VectorSearchPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(VectorSearchPlugin::make());
}
```

## Features

- Vector index and document CRUD resources
- Live semantic search playground dashboard
- Pipeline and engine stats widgets

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
