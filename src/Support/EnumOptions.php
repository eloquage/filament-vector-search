<?php

declare(strict_types=1);

namespace Eloquage\FilamentVectorSearch\Support;

use BackedEnum;
use Eloquage\LaravelVectorSearch\Contracts\HasLabel;

final class EnumOptions
{
    /**
     * @param  array<BackedEnum&HasLabel>  $cases
     * @return array<string, string>
     */
    public static function from(array $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}
