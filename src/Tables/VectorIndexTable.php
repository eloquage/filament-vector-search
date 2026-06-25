<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Tables;

use Eloquage\FilamentVectorSearch\Support\EnumOptions;
use Eloquage\LaravelVectorSearch\Enums\IndexDriver;
use Filament\Tables\Filters\SelectFilter;

final class VectorIndexTable
{
    /**
     * @return array<int, SelectFilter>
     */
    public static function filters(): array
    {
        return [
            SelectFilter::make('driver')
                ->options(EnumOptions::from(IndexDriver::cases())),
        ];
    }
}
