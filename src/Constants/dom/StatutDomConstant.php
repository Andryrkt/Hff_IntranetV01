<?php

namespace App\Constants\dom;

class StatutDomConstant
{
    public const STATUT_ATTENTE_PAIEMENT      = "ATTENTE PAIEMENT";       // OUV -- 175
    public const STATUT_CONTROLE_SERVICE      = "CONTROLE SERVICE";       // OUV -- 2
    public const STATUT_OUVERT                = "OUVERT";                 // OUV -- 27
    public const STATUT_PRE_CONTROLE_ATELIER  = "PRE-CONTROLE ATELIER";   // OUV -- 1
    public const STATUT_TRAITE_PAR_COMPTA     = "TRAITE PAR COMPTA";      // OUV -- 261
    public const STATUT_VALIDATION_COMPTA     = "VALIDATION COMPTA";      // OUV -- 1
    public const STATUT_VALIDATION_DG         = "VALIDATION DG";          // OUV -- 
    public const STATUT_VALIDATION_RH         = "VALIDATION RH";          // OUV -- 6
    public const STATUT_VALIDE                = "VALIDE";                 // OUV -- 66
    public const STATUT_VALIDE_COMPTABILITE   = "VALIDE COMPTABILITE";    // OUV -- 5
    public const STATUT_ENCOURS               = "ENCOURS";                // ENC -- 
    public const STATUT_COMPTA                = "COMPTA";                 // CPT -- 
    public const STATUT_ANNULE                = "ANNULE";                 // ANN -- 36
    public const STATUT_ANNULE_CHEF_SERVICE   = "ANNULE CHEF DE SERVICE"; // ANN -- 293
    public const STATUT_ANNULE_COMPTABILITE   = "ANNULE COMPTABILITE";    // ANN -- 120
    public const STATUT_ANNULE_DG             = "ANNULE DG";              // ANN -- 3
    public const STATUT_ANNULE_RH             = "ANNULE RH";              // ANN -- 8
    public const STATUT_ANNULE_SECRETARIAT_RH = "ANNULE SECRETARIAT RH";  // ANN -- 3
    public const STATUT_PAYE                  = "PAYE";                   // PAY -- 12771
    public const STATUT_CLOTURE               = "CLOTURE";                // CLO -- 

    private const CSS_CLASS_MAP = [
        // OUVERT / EN COURS DE TRAITEMENT
        self::STATUT_OUVERT                => 'bg-warning bg-gradient text-center',
        self::STATUT_PRE_CONTROLE_ATELIER  => 'bg-warning bg-gradient text-center',
        self::STATUT_CONTROLE_SERVICE      => 'bg-info',
        self::STATUT_ENCOURS               => 'bg-info',
        self::STATUT_COMPTA                => 'bg-primary',
        self::STATUT_TRAITE_PAR_COMPTA     => 'bg-primary',

        // VALIDATIONS
        self::STATUT_VALIDATION_COMPTA     => 'bg-success bg-opacity-50',
        self::STATUT_VALIDATION_RH         => 'bg-success bg-opacity-50',
        self::STATUT_VALIDATION_DG         => 'bg-success bg-opacity-50',
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
