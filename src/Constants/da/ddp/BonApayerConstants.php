<?php

namespace App\Constants\da\ddp;

final class BonApayerConstants
{
    public const STATUT_A_TRANSMETTERE = 'A transmettre';
    public const STATUT_TRANSMISE = 'Transmise';
    public const STATUT_CLOTUREE = 'Clôturée';
    public const STATUT_REFUSEE = 'Refusée';

    public const CSS_CLASS_MAP = [
        self::STATUT_A_TRANSMETTERE => 'statut-a-transmettre',
        self::STATUT_TRANSMISE => 'statut-transmise',
        self::STATUT_CLOTUREE => 'statut-cloturee',
        self::STATUT_REFUSEE => 'statut-refusee',
    ];

    /**
     * Retourne la classe CSS pour un statut donné
     */
    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
