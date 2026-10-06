<?php

namespace App\Service\autres;

class VersionService
{
    /**
     * Incrémente un entier nullable.
     * Si la valeur est null, retourne 1.
     *
     * @param int|null $num
     * @return int
     */
    public static function autoIncrement(?int $num): int
    {
        return ($num ?? 0) + 1;
    }
}
