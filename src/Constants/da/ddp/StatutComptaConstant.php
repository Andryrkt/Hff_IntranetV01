<?php

namespace App\Constants\da\ddp;

final class StatutComptaConstant
{
    public const DDPA_SOUMIS_A_VALIDATION = 'DDPA soumis à validation';
    public const BAP_A_SOUMETTRE = 'BAP à soumettre';
    public const VALIDE = 'Validé';
    public const SOUMIS_A_VALIDATION = 'Soumis à validation';
    public const REFUSE_CHEF_DE_SERVICE = 'Refusé chef de service';

    public const CSS_CLASS_MAP = [
        self::DDPA_SOUMIS_A_VALIDATION => 'ddpa-soumis-validation',
        self::BAP_A_SOUMETTRE => 'bap-a-soumettre',
        self::VALIDE => 'valide',
        self::SOUMIS_A_VALIDATION => 'soumis-validation',
        self::REFUSE_CHEF_DE_SERVICE => 'refuse-chef-de-service',
    ];

    /**
     * Retourne la classe CSS pour un statut donné
     */
    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
