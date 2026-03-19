<?php

namespace App\Constants\Magasin\Devis;

class StatutBcNegConstant
{
    public const SOUMIS_VALIDATION = 'Soumis à validation';
    public const EN_ATTENTE_BC = 'En attente bc';
    public const VALIDER = 'Validé';

    public const CSS_CLASS_MAP_STATUT_BC = [
        self::SOUMIS_VALIDATION => 'bg-bc-soumis-validation',
        self::EN_ATTENTE_BC => 'bg-bc-en-attente',
        self::VALIDER => 'bg-bc-valide'
    ];

    public static function getCssClassBC(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_BC[$statut] ?? '';
    }
}
