<?php

namespace App\Model\Traits;

trait ConversionModel
{
    public function convertirEnUtf8($element)
    {
        if (is_array($element)) {
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertirEnUtf8($value);
            }
        } elseif (is_string($element)) {
            // return mb_convert_encoding($element, 'UTF-8', 'ISO-8859-1');
            return iconv('ISO-8859-1', 'UTF-8', $element);
        }
        return $element;
    }

    public function clean_string($string)
    {
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }

    private function clean_string_1($string)
    {
        return mb_convert_encoding($string, 'ASCII', 'UTF-8');
    }

    public function TestCaractereSpeciaux(array $tab)
    {
        function contains_special_characters($string)
        {
            // Expression régulière pour vérifier les caractères spéciaux
            return preg_match('/[^\x20-\x7E\t\r\n]/', $string);
        }

        // Parcours de chaque élément du tableau $tab
        foreach ($tab as $key => $value) {
            // Parcours de chaque valeur de l'élément
            foreach ($value as $inner_value) {
                // Vérification de la présence de caractères spéciaux
                if (contains_special_characters($inner_value)) {
                    echo "Caractère spécial trouvé dans la valeur : $inner_value<br>";
                }
            }
        }
    }

    /**
     * c'est une foncion qui décode les caractères speciaux en html
     */
    public function decode_entities_in_array($array)
    {
        // Parcourir chaque élément du tableau
        foreach ($array as $key => $value) {
            // Si la valeur est un tableau, appeler récursivement la fonction
            if (is_array($value)) {
                $array[$key] = $this->decode_entities_in_array($value);
            } else {
                // Si la valeur est une chaîne, appliquer la fonction decode_entities()
                $array[$key] = html_entity_decode($value);
            }
        }
        return $array;
    }
}
