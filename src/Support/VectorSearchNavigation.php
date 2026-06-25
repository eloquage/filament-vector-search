<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Support;

use UnitEnum;

class VectorSearchNavigation
{
    public const string CONFIG_KEY = 'filament-vector-search.navigation_group';

    public const string DEFAULT_GROUP = 'Vector Search';

    public static function group(): string|UnitEnum|null
    {
        return config(self::CONFIG_KEY, self::DEFAULT_GROUP);
    }

    public static function setGroup(string|UnitEnum|null $group): void
    {
        config([self::CONFIG_KEY => $group]);
    }

    public static function groupLabel(): ?string
    {
        $group = self::group();

        if ($group === null) {
            return null;
        }

        if ($group instanceof UnitEnum) {
            return $group instanceof \BackedEnum ? (string) $group->value : $group->name;
        }

        return $group;
    }
}
