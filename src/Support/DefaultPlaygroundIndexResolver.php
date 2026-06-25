<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Support;

use Eloquage\LaravelVectorSearch\Models\VectorIndex;

class DefaultPlaygroundIndexResolver
{
    public function resolve(): ?string
    {
        foreach ($this->configuredIndexNames() as $name) {
            $id = VectorIndex::query()->where('name', $name)->value('id');

            if ($id !== null) {
                return (string) $id;
            }
        }

        $fallback = VectorIndex::query()->orderBy('name')->value('id');

        return $fallback !== null ? (string) $fallback : null;
    }

    /**
     * @return list<string>
     */
    private function configuredIndexNames(): array
    {
        $names = [];

        if (ArchiveIntegration::isEnabled()) {
            $archiveName = ArchiveIntegration::defaultIndexName();

            if ($archiveName !== null) {
                $names[] = $archiveName;
            }
        }

        $configKeys = config('filament-vector-search.archive.default_index_config_keys', []);

        if (! is_array($configKeys)) {
            return array_values(array_unique($names));
        }

        foreach ($configKeys as $key) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $value = config($key);

            if (is_string($value) && $value !== '') {
                $names[] = $value;
            }
        }

        return array_values(array_unique($names));
    }
}
