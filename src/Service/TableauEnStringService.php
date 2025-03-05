<?php

namespace App\Service;

use InvalidArgumentException;

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

     /**
     * Methode general pour transformer un tableau en string
     *
     * @param array $tab
     * @return string
     */
    public static function TableauEnString(string $separateur, array $tab, string $quote = "'"): string
    {
        // Fonction de validation et de transformation
        $flattenedArray = self::flattenArray($tab);

        // Si le tableau est vide, renvoyer deux quotes simples
        if (empty($flattenedArray)) {
            return $quote . $quote;
        }
        
        // Échappe les caractères spéciaux si nécessaire
        $escapedArray = array_map(function ($el) use ($quote) {
            // Convertir en chaîne de caractères si ce n'est pas déjà une
            if (!is_scalar($el)) {
                throw new InvalidArgumentException("Tous les éléments du tableau doivent être scalaires.");
            }
            return $quote . $el . $quote;
        }, $flattenedArray);
        
        // Joindre les éléments avec le séparateur
        return implode($separateur, $escapedArray);
    }
}
