<?php

declare(strict_types=1);

return [
    'default_playground_index' => env('FILAMENT_VECTOR_SEARCH_DEFAULT_INDEX'),

    'archive' => [
        'enabled' => env('FILAMENT_VECTOR_SEARCH_ARCHIVE_INTEGRATION', true),

        'default_index_config_keys' => [
            'filament-vector-search.default_playground_index',
            'document-archive.vector_index',
            'document-archive-mcp.vector_index',
        ],

        'metadata_keys' => [
            'collection',
            'canonical_basename',
            'source_file',
            'status',
            'case_number',
        ],
    ],

    'widgets' => [
        'polling_interval' => env('FILAMENT_VECTOR_SEARCH_WIDGET_POLLING', '30s'),
    ],
];
