<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tables;

use Eloquage\FilamentVectorSearch\Support\ArchiveIntegration;

final class VectorDocumentTableConfiguration
{
    public function __construct(
        public bool $includeIndexColumn = true,
        public bool $searchableTitle = true,
        public bool $sortableTitle = true,
        public bool $sortableUpdatedAt = true,
        public bool $includeArchiveColumns = false,
    ) {}

    public static function forResource(): self
    {
        return new self(
            includeIndexColumn: true,
            searchableTitle: true,
            sortableTitle: true,
            sortableUpdatedAt: true,
            includeArchiveColumns: ArchiveIntegration::isAvailable(),
        );
    }

    public static function forRelationManager(): self
    {
        return new self(
            includeIndexColumn: false,
            searchableTitle: true,
            sortableTitle: false,
            sortableUpdatedAt: false,
            includeArchiveColumns: false,
        );
    }
}
