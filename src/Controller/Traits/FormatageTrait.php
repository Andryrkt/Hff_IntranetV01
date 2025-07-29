<?php

namespace App\Controller\Traits;

trait FormatageTrait
{
    private function formatageDate($date)
    {
        return implode('/', array_reverse(explode('-', $date)));
    }

    private function formatDateTime($dateTime)
    {
        $date = new \DateTime($dateTime);
        $formattedDate = $date->format('d/m/Y');

        return $formattedDate;
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

        // Limite la partie décimale à deux chiffres
        $decPart = substr($decPart, 0, 2);

        // Réassemble le nombre
        if ($decPart !== '') {
            return $intPartWithDots . ',' . $decPart;
        } else {
            return $intPartWithDots;
        }
    }

    private function formatNumberGeneral($number, $separateurMillier = '.', $separateurDecimal = ',', $precision = 2)
    {
        // Convertit le nombre en chaîne de caractères
        $numberStr = (string)$number;

        // Remplace le séparateur décimal en fonction des paramètres
        $numberStr = str_replace(['.', ','], $separateurDecimal === ',' ? [',', '.'] : ['.', ','], $numberStr);

        // Sépare la partie entière et la partie décimale
        if (strpos($numberStr, $separateurDecimal) !== false) {
            list($intPart, $decPart) = explode($separateurDecimal, $numberStr);
        } else {
            $intPart = $numberStr;
            $decPart = '';
        }

        // Nettoie la partie entière et la convertit en entier
        $intPart = intval(str_replace($separateurMillier, '', $intPart));

        // Formate la partie entière avec le séparateur des milliers
        $intPartWithSeparator = number_format($intPart, 0, '', $separateurMillier);

        // Limite ou ajoute des zéros à la partie décimale pour respecter la précision
        $decPart = str_pad(substr($decPart, 0, $precision), $precision, '0', STR_PAD_RIGHT);

        // Réassemble le nombre avec la partie entière et décimale
        if ($precision > 0) {
            return $intPartWithSeparator . $separateurDecimal . $decPart;
        } else {
            return $intPartWithSeparator;
        }
    }


    private function formatNumberDecimal($number)
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

        // Limite la partie décimale à deux chiffres
        $decPart = substr($decPart, 0, 2);

        // Si la partie décimale a un seul chiffre, on ajoute un 0
        if (strlen($decPart) === 1) {
            $decPart .= '0';
        }
        // Si la partie décimale est vide, on ajoute '00'
        if ($decPart === '') {
            $decPart = '00';
        }

        // Réassemble le nombre
        if ($decPart !== '') {
            return $intPartWithDots . ',' . $decPart;
        } else {
            return $intPartWithDots;
        }
    }

    private function chaine_vers_nombre(?string $chaine = null)
    {
        if ($chaine) {
            // Supprimer les points (séparateurs de milliers)
            $sans_points = str_replace('.', '', $chaine);

            // Remplacer la virgule (séparateur décimal) par un point
            $standard = str_replace(',', '.', $sans_points);

            // Vérifier si c'est un float ou un int
            return (strpos($standard, '.') !== false) ? (float)$standard : (int)$standard;
        }
        return $chaine;
    }
}
