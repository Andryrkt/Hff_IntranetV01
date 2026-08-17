<?php

namespace App\Constants\dom;

class StatutDomConstant
{
    public const STATUT_ATTENTE_PAIEMENT      = "ATTENTE PAIEMENT";
    public const STATUT_CONTROLE_SERVICE      = "CONTROLE SERVICE";
    public const STATUT_OUVERT                = "OUVERT";
    public const STATUT_PRE_CONTROLE_ATELIER  = "PRE-CONTROLE ATELIER";
    public const STATUT_TRAITE_PAR_COMPTA     = "TRAITE PAR COMPTA";
    public const STATUT_VALIDATION_COMPTA     = "VALIDATION COMPTA";
    public const STATUT_VALIDATION_DG         = "VALIDATION DG";
    public const STATUT_VALIDATION_RH         = "VALIDATION RH";
    public const STATUT_VALIDE                = "VALIDE";
    public const STATUT_VALIDE_COMPTABILITE   = "VALIDE COMPTABILITE";
    public const STATUT_ENCOURS               = "ENCOURS";
    public const STATUT_COMPTA                = "COMPTA";
    public const STATUT_ANNULE                = "ANNULE";
    public const STATUT_ANNULE_CHEF_SERVICE   = "ANNULE CHEF DE SERVICE";
    public const STATUT_ANNULE_COMPTABILITE   = "ANNULE COMPTABILITE";
    public const STATUT_ANNULE_DG             = "ANNULE DG";
    public const STATUT_ANNULE_RH             = "ANNULE RH";
    public const STATUT_ANNULE_SECRETARIAT_RH = "ANNULE SECRETARIAT RH";
    public const STATUT_PAYE                  = "PAYE";
    public const STATUT_CLOTURE               = "CLOTURE";

    private const CSS_CLASS_MAP = [
        // OUVERT / EN COURS DE TRAITEMENT
        self::STATUT_OUVERT                => 'bg-warning bg-gradient text-center',
        self::STATUT_PRE_CONTROLE_ATELIER  => 'bg-warning bg-gradient',
        self::STATUT_CONTROLE_SERVICE      => 'bg-info',
        self::STATUT_ENCOURS               => 'bg-info',
        self::STATUT_COMPTA                => 'bg-primary',
        self::STATUT_TRAITE_PAR_COMPTA     => 'bg-primary',

        // VALIDATIONS
        self::STATUT_VALIDATION_COMPTA     => 'bg-success',
        self::STATUT_VALIDATION_RH         => 'bg-success',
        self::STATUT_VALIDATION_DG         => 'bg-success',
        self::STATUT_VALIDE                => 'bg-success',
        self::STATUT_VALIDE_COMPTABILITE   => 'bg-success',

        // ATTENTE DE PAIEMENT
        self::STATUT_ATTENTE_PAIEMENT      => 'bg-success bg-opacity-50',

        // PAYÉ / TERMINÉ
        self::STATUT_PAYE                  => 'bg-success bg-gradient',
        self::STATUT_CLOTURE               => 'bg-dark',

        // ANNULATIONS
        self::STATUT_ANNULE                => 'bg-danger',
        self::STATUT_ANNULE_RH             => 'bg-danger',
        self::STATUT_ANNULE_DG             => 'bg-danger',
        self::STATUT_ANNULE_CHEF_SERVICE   => 'bg-danger',
        self::STATUT_ANNULE_COMPTABILITE   => 'bg-danger',
        self::STATUT_ANNULE_SECRETARIAT_RH => 'bg-danger',
    ];

    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
