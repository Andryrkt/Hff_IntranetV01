<?php

namespace App\Service\dit\transfer;

class ConversionService
{
    public static function convertToDateTime(string $dateString, string $format = 'Y-m-d'): ?\DateTime
    {
        $dateTime = \DateTime::createFromFormat($format, $dateString);
        return ($dateTime && $dateTime->format($format) === $dateString) ? $dateTime : null;
    }

    public static function convertToHHMM(string $time)
    {
        // Convertit le temps en chaîne de 6 caractères si ce n'est pas déjà le cas
        $time = str_pad($time, 6, "0", STR_PAD_LEFT);

        // Récupère les heures et minutes
        $hours = substr($time, 0, 2);
        $minutes = substr($time, 2, 2);

        // Format final
        return $hours . ":" . $minutes;
    }
}