<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tables;

final class VectorDocumentTableConfiguration
{
    public function __construct(
        public bool $includeIndexColumn = true,
        public bool $searchableTitle = true,
        public bool $sortableTitle = true,
        public bool $sortableUpdatedAt = true,
    ) {}

    public static function forResource(): self
    {
        return new self(
            includeIndexColumn: true,
            searchableTitle: true,
            sortableTitle: true,
            sortableUpdatedAt: true,
        );
    }

    public static function forRelationManager(): self
    {
        return new self(
            includeIndexColumn: false,
            searchableTitle: true,
            sortableTitle: false,
            sortableUpdatedAt: false,
        );
    }
}
