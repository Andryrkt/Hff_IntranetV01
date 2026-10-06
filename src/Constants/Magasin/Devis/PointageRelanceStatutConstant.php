<?php

namespace App\Constants\Magasin\Devis;

class PointageRelanceStatutConstant
{
    public const POINTAGE_RELANCE_A_RELANCER = 'A relancer';
    
    /**
     * Note : Le statut 'Relancé' n'existe pas en tant que chaîne dans les données.
     * Il est représenté par une date de relance.
     * Cette constante est utilisée uniquement comme clé pour le mapping CSS ci-dessous.
     */
    public const POINTAGE_RELANCE_DATE = 'DATE_RELANCE';

    public const CSS_CLASS_MAP_STATUT_PR1 = [
        self::POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
        self::POINTAGE_RELANCE_DATE => 'bg-warning'
    ];

    public const CSS_CLASS_MAP_STATUT_PR2 = [
        self::POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
        self::POINTAGE_RELANCE_DATE => 'bg-warning'
    ];

    public const CSS_CLASS_MAP_STATUT_PR3 = [
        self::POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
        self::POINTAGE_RELANCE_DATE => 'bg-warning'
    ];

    public static function getCssClassPR1(string $statut): string
    {
        return self::resolveCssClass($statut, self::CSS_CLASS_MAP_STATUT_PR1);
    }

    public static function getCssClassPR2(string $statut): string
    {
        return self::resolveCssClass($statut, self::CSS_CLASS_MAP_STATUT_PR2);
    }

    public static function getCssClassPR3(string $statut): string
    {
        return self::resolveCssClass($statut, self::CSS_CLASS_MAP_STATUT_PR3);
    }

    /**
     * Détermine la classe CSS en fonction du statut (vide, "A relancer", ou une date).
     */
    private static function resolveCssClass(string $statut, array $map): string
    {
        if ($statut === '') {
            return '';
        }

        if ($statut === self::POINTAGE_RELANCE_A_RELANCER) {
            return $map[self::POINTAGE_RELANCE_A_RELANCER] ?? '';
        }

        // Si ce n'est pas vide et pas "A relancer", c'est une date
        return $map[self::POINTAGE_RELANCE_DATE] ?? '';
    }
}
