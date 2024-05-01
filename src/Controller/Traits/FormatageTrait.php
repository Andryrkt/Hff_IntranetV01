<?php

namespace App\Controller\Traits;

trait FormatageTrait
{
    private function formatageDate($date)
    {
        return implode('/', array_reverse(explode('-', $date)));
    }

    
    private function formatNumber($number)
    {

        // Convertit le nombre en chaîne de caractères pour manipulation
        $numberStr = (string)$number;
        $numberStr = str_replace('.', ',', $numberStr);
        // Sépare la partie entière et la partie décimale
        if (strpos($numberStr, ',') !== false) {
            list($intPart, $decPart) = explode(',', $numberStr);
        } else {
            $intPart = $numberStr;
            $decPart = '';
        }

        // Convertit la partie entière en float pour éviter l'avertissement
        $intPart = floatval(str_replace('.', '', $intPart));

        // Formate la partie entière avec des points pour les milliers
        $intPartWithDots = number_format($intPart, 0, ',', '.');

        // Réassemble le nombre
        if ($decPart !== '') {
            return $intPartWithDots . ',' . $decPart;
        } else {
            return $intPartWithDots;
        }
    }
}