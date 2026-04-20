<?php

namespace App\Constants\ddp;

class StatutConstants
{
    public const DDPL_A_TRANSMETTRE = 'DDPL à transmettre';
    public const BAP_A_TRANSMETTRE = 'BAP à transmettre';
    public const DDPR_A_TRANSMETTRE = 'DDPR à transmettre';
    public const DDPA_A_TRANSMETTRE = 'DDPA à transmettre';
    public const DDPA_SOUMIS_A_VALIDATION = 'DDPA soumis à validation';
    public const STATUT_SOUMIS_A_VALIDATION = 'Soumis à validation';
    // public const STATUT_EN_ATTENTE_VALIDATION_BC = 'En attente validation BC';
    // public const STATUT_A_CONFIRMER = 'A confirmer';
    public const VALIDE = 'Validé';

    public const STATUT_A_TRANSMETTRE = [
        self::DDPL_A_TRANSMETTRE,
        self::BAP_A_TRANSMETTRE,
        self::DDPR_A_TRANSMETTRE,
        self::DDPA_A_TRANSMETTRE
    ];

    public const CSS_CLASS_MAP = [
        self::DDPL_A_TRANSMETTRE => 'statut-ddpl-a-transférer',
        self::BAP_A_TRANSMETTRE => 'statut-bap-a-transmettre',
        self::DDPR_A_TRANSMETTRE => 'statut-ddpr-a-transmettre',
        self::DDPA_A_TRANSMETTRE => 'statut-ddpa-a-transmettre',
        self::DDPA_SOUMIS_A_VALIDATION => 'statut-soumis-a-validation',
        self::STATUT_SOUMIS_A_VALIDATION => 'statut-soumis-a-validation',
        // self::STATUT_EN_ATTENTE_VALIDATION_BC => 'statut-en-attente-validation-bc',
        // self::STATUT_A_CONFIRMER => 'statut-a-confirmer',
        self::VALIDE => 'statut-valide',
    ];

    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
