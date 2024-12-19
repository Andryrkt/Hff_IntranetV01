<?php

namespace App\Service;

class TableauEnStringService
{
    /**
     * Convertit un tableau multidimensionnel en une chaîne formatée avec des guillemets simples.
     *
     * @param array $tab
     * @return string
     */
    public static function orEnString(array $tab): string
    {
        $flattenedArray = self::flattenArray($tab);

        return "'" . implode("','", $flattenedArray) . "'";
    }

    /**
     * Transforme un tableau multidimensionnel en un tableau unidimensionnel.
     *
     * @param array $tabs
     * @return array
     */
    private static function flattenArray(array $tabs): array
    {
        $result = [];
        foreach ($tabs as $values) {
            if (is_array($values)) {
                // Fusionne les sous-tableaux récursivement
                $result = array_merge($result, self::flattenArray($values));
            } else {
                $result[] = (string) $values; // Convertit les valeurs en chaînes
            }
        }

        return $result;
    }
}
