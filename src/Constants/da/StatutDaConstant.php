<?php

namespace App\Constants\da;

class StatutDaConstant
{
    public const STATUT_VALIDE               = 'Bon d’achats validé';       /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // cliquable par Admin et Appro
    public const STATUT_CLOTUREE             = 'Clôturée';                  /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // ! non cliquable par quiconque
    public const STATUT_CLOTUREE_HORS_DELAI  = 'Clôturée hors délai';                  /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // ! non cliquable par quiconque
    public const STATUT_REFUSE_APPRO         = 'Refusé appro';              /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // ! non cliquable par quiconque
    public const STATUT_TERMINER             = 'TERMINER';                  /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // ! non cliquable par quiconque
    public const STATUT_EN_COURS_CREATION    = 'En cours de création';      /*_________ DA via OR ________*/ /*_ statut_dal _*/ // cliquable par Admin et Atelier
    public const STATUT_SOUMIS_APPRO         = 'Demande d’achats';          /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // cliquable par Admin et Appro
    public const STATUT_DEMANDE_DEVIS        = 'Demande de devis en cours'; /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // cliquable par Admin et Appro
    public const STATUT_DEVIS_A_RELANCER     = 'Devis à relancer APP';      /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // cliquable par Admin et Appro
    public const STATUT_AUTORISER_EMETTEUR   = 'Demande initiale à modifier'; /*_________ DA via OR ________*/ /*_ statut_dal _*/ // cliquable par Admin et Atelier
    public const STATUT_EN_COURS_PROPOSITION = 'En cours de proposition';   /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // cliquable par Admin et Appro
    public const STATUT_SOUMIS_ATE           = 'Proposition achats';        /*__ DA direct et DA via OR __*/ /*_ statut_dal _*/ // cliquable par Admin et (Atelier ou service emetteur) et Appro
    public const STATUT_DW_A_VALIDE          = 'A valider chef de service'; /*_________ DA direct ________*/ /*__ statut_or _*/ // ! non cliquable par quiconque
    public const STATUT_DW_REFUSEE           = 'DA refusée';                /*_________ DA direct ________*/ /*__ statut_or _*/ // ! non cliquable par quiconque
    public const STATUT_DW_VALIDEE           = 'DA validée';                /*_________ DA direct ________*/ /*__ statut_or _*/ // cliquable par Admin et Appro
    public const STATUT_DW_A_MODIFIER        = 'DA à modifier';

    public const TRAITEMENT_APPRO           = 'Traitement appro';

    public const STATUT_DA = [
        self::STATUT_EN_COURS_CREATION    => self::STATUT_EN_COURS_CREATION,
        self::STATUT_SOUMIS_APPRO         => self::STATUT_SOUMIS_APPRO, // demande d'achat
        self::STATUT_DEMANDE_DEVIS        => self::STATUT_DEMANDE_DEVIS,
        self::STATUT_DEVIS_A_RELANCER     => self::STATUT_DEVIS_A_RELANCER,
        self::STATUT_AUTORISER_EMETTEUR   => self::STATUT_AUTORISER_EMETTEUR, // Création demande initiale
        self::STATUT_EN_COURS_PROPOSITION => self::STATUT_EN_COURS_PROPOSITION,
        self::STATUT_SOUMIS_ATE           => self::STATUT_SOUMIS_ATE, // proposition d'achat
        self::STATUT_VALIDE               => self::STATUT_VALIDE, // Bon d'achats validé
        self::STATUT_CLOTUREE             => self::STATUT_CLOTUREE,
        self::STATUT_CLOTUREE_HORS_DELAI  => self::STATUT_CLOTUREE_HORS_DELAI,
    ];

    public const STATUT_TRAITEMENT_APPRO = [
        self::STATUT_DEMANDE_DEVIS        => self::STATUT_DEMANDE_DEVIS,
        self::STATUT_DEVIS_A_RELANCER     => self::STATUT_DEVIS_A_RELANCER,
        self::STATUT_EN_COURS_PROPOSITION => self::STATUT_EN_COURS_PROPOSITION,
    ];

    public const STATUT_DA_PAS_APPRO_NI_ADMIN = [
        self::STATUT_EN_COURS_CREATION    => self::STATUT_EN_COURS_CREATION,
        self::STATUT_SOUMIS_APPRO         => self::STATUT_SOUMIS_APPRO, // demande d'achat
        self::TRAITEMENT_APPRO            => self::TRAITEMENT_APPRO,
        self::STATUT_AUTORISER_EMETTEUR   => self::STATUT_AUTORISER_EMETTEUR, // Création demande initiale
        self::STATUT_SOUMIS_ATE           => self::STATUT_SOUMIS_ATE, // proposition d'achat
        self::STATUT_VALIDE               => self::STATUT_VALIDE, // Bon d'achats validé
        self::STATUT_CLOTUREE             => self::STATUT_CLOTUREE,
        self::STATUT_CLOTUREE_HORS_DELAI  => self::STATUT_CLOTUREE_HORS_DELAI,
    ];

    public const STATUT_DAL = [
        'enregistrerBrouillon' => StatutDaConstant::STATUT_EN_COURS_CREATION,
        'soumissionAppro'      => StatutDaConstant::STATUT_SOUMIS_APPRO,
    ];

    public const CSS_CLASS_MAP_STATUT_DA = [
        self::STATUT_VALIDE               => 'bg-bon-achat-valide',
        self::STATUT_CLOTUREE             => 'bg-bon-achat-valide',
        self::STATUT_TERMINER             => 'bg-primary text-white',
        self::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
        self::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
        self::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
        self::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
        self::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
        self::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
        self::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
        self::STATUT_AUTORISER_EMETTEUR   => 'bg-creation-demande-initiale',
        self::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
        self::STATUT_CLOTUREE_HORS_DELAI  => 'bg-bc-pas-dans-or',
    ];

    public static function getCssClassDa(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_DA[$statut] ?? '';
    }
}
