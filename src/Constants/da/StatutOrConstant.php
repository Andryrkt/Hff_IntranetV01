<?php

namespace App\Constants\da;

class StatutOrConstant
{
    public const STATUT_VIDE                       = '';
    public const STATUT_A_RESOUMETTRE_A_VALIDATION = 'A resoumettre à validation';
    public const STATUT_A_VALIDER_CA               = 'A valider chef atelier';
    public const STATUT_A_VALIDER_CLIENT           = 'A valider client interne';
    public const STATUT_A_VALIDER_DT               = 'A valider directeur technique';
    public const STATUT_MODIF_DEMANDE_PAR_CA       = 'Modification demandée par CA';
    public const STATUT_MODIF_DEMANDE_PAR_CLIENT   = 'Modification demandée par client';
    public const STATUT_REFUSE_CA                  = 'Refusé chef atelier';
    public const STATUT_REFUSE_CLIENT              = 'Refusé client interne';
    public const STATUT_REFUSE_DT                  = 'Refusé DT';
    public const STATUT_SOUMIS_A_VALIDATION        = 'Soumis à validation';
    public const STATUT_VALIDE                     = 'Validé';


    public const STATUT_DW_A_VALIDE          = 'A valider chef de service'; /*_________ DA direct ________*/ /*__ statut_or _*/ // ! non cliquable par quiconque
    public const STATUT_DW_REFUSEE           = 'DA refusée';                /*_________ DA direct ________*/ /*__ statut_or _*/ // ! non cliquable par quiconque
    public const STATUT_DW_VALIDEE           = 'DA validée';                /*_________ DA direct ________*/ /*__ statut_or _*/ // cliquable par Admin et Appro
    public const STATUT_DW_A_MODIFIER        = 'DA à modifier';             /*_________ DA direct ________*/ /*__ statut_or _*/ // cliquable par Admin et service emetteur et Appro

    public const STATUT_OR = [
        'OR - ' . self::STATUT_VALIDE              => self::STATUT_VALIDE,
        'OR - ' . self::STATUT_A_VALIDER_CA        => self::STATUT_A_VALIDER_CA,
        'OR - ' . self::STATUT_A_VALIDER_CLIENT    => self::STATUT_A_VALIDER_CLIENT,
        'OR - ' . self::STATUT_REFUSE_CA           => self::STATUT_REFUSE_CA,
        'OR - ' . self::STATUT_REFUSE_CLIENT       => self::STATUT_REFUSE_CLIENT,
        'OR - ' . self::STATUT_REFUSE_DT           => self::STATUT_REFUSE_DT,
        'OR - ' . self::STATUT_SOUMIS_A_VALIDATION => self::STATUT_SOUMIS_A_VALIDATION,
        self::STATUT_DW_A_VALIDE                              => self::STATUT_DW_A_VALIDE,
        self::STATUT_DW_VALIDEE                               => self::STATUT_DW_VALIDEE,
        self::STATUT_DW_A_MODIFIER                            => self::STATUT_DW_A_MODIFIER,
        self::STATUT_DW_REFUSEE                               => self::STATUT_DW_REFUSEE,
    ];

    public const CSS_CLASS_MAP_STATUT_OR = [
        'OR - ' . self::STATUT_VALIDE                     => 'bg-or-valide',
        'OR - ' . self::STATUT_A_RESOUMETTRE_A_VALIDATION => 'bg-a-resoumettre-a-validation',
        'OR - ' . self::STATUT_A_VALIDER_CA               => 'bg-or-valider-ca',
        'OR - ' . self::STATUT_A_VALIDER_DT               => 'bg-or-valider-dt',
        'OR - ' . self::STATUT_A_VALIDER_CLIENT           => 'bg-or-valider-client',
        'OR - ' . self::STATUT_MODIF_DEMANDE_PAR_CA       => 'bg-modif-demande-ca',
        'OR - ' . self::STATUT_MODIF_DEMANDE_PAR_CLIENT   => 'bg-modif-demande-client',
        'OR - ' . self::STATUT_REFUSE_CA                  => 'bg-or-non-valide',
        'OR - ' . self::STATUT_REFUSE_CLIENT              => 'bg-or-non-valide',
        'OR - ' . self::STATUT_REFUSE_DT                  => 'bg-or-non-valide',
        'OR - ' . self::STATUT_SOUMIS_A_VALIDATION        => 'bg-or-soumis-validation',
        self::STATUT_DW_A_VALIDE                           => 'bg-or-soumis-validation',
        self::STATUT_DW_VALIDEE                            => 'bg-or-valide',
        self::STATUT_DW_A_MODIFIER                         => 'bg-modif-demande-client',
        self::STATUT_DW_REFUSEE                            => 'bg-or-non-valide',
    ];

    public static function getCssClassOr(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_OR[$statut] ?? '';
    }
}
