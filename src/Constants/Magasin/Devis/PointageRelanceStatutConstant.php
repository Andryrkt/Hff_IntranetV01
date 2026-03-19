<?php

namespace App\Constants\Magasin\Devis;

class PointageRelanceStatutConstant
{
    public const POINTAGE_RELANCE_A_RELANCER = 'A relancer';
    public const POINTAGE_RELANCE_RELANCE = 'Relancé';

    public const CSS_CLASS_MAP_STATUT_PR1 = [
        self::POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
        self::POINTAGE_RELANCE_RELANCE => 'bg-warning'
    ];
    public const CSS_CLASS_MAP_STATUT_PR2 = [
        self::POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
        self::POINTAGE_RELANCE_RELANCE => 'bg-warning'
    ];

    public const CSS_CLASS_MAP_STATUT_PR3 = [
        self::POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
        self::POINTAGE_RELANCE_RELANCE => 'bg-warning'
    ];


    public static function getCssClassPR1(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_PR1[$statut] ?? '';
    }

    public static function getCssClassPR2(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_PR2[$statut] ?? '';
    }

    public static function getCssClassPR3(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_PR3[$statut] ?? '';
    }
}
