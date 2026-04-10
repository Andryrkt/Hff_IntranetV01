<?php

namespace App\Constants\da\ddp;

final class StatutComptaConstant
{
    public const DDPA_SOUMIS_A_VALIDATION = 'DDPA soumis à validation';
    public const BAP_SOUMIS_A_VALIDATION = 'BAP soumis à validation';



    public const CSS_CLASS_MAP = [];

    /**
     * Retourne la classe CSS pour un statut donné
     */
    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
