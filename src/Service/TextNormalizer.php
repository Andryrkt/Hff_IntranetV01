<?php

namespace App\Service;

final class TextNormalizer
{
    private const REPLACEMENTS = [
        'œ' => 'oe',
        'Œ' => 'OE',
        '’' => "'",
    ];

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, self::REPLACEMENTS);
    }
}
