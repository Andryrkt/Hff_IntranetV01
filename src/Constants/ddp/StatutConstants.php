<?php

namespace App\Constants\ddp;

class StatutConstants
{
    public const STATUT_SOUMIS_A_VALIDATION = 'Soumis à validation';
    public const STATUT_EN_ATTENTE_VALIDATION_BC = 'En attente validation BC';
    public const STATUT_A_CONFIRMER = 'A confirmer';

    public const CSS_CLASS_MAP = [
        self::STATUT_SOUMIS_A_VALIDATION => 'statut-soumis-a-validation',
        self::STATUT_EN_ATTENTE_VALIDATION_BC => 'statut-en-attente-validation-bc',
        self::STATUT_A_CONFIRMER => 'statut-a-confirmer',
    ];

    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
