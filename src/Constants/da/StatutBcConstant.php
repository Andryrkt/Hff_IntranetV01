<?php

namespace App\Constants\da;

class StatutBcConstant
{
    public const STATUT_A_GENERER                = 'A générer';
    public const STATUT_A_EDITER                 = 'A éditer';
    public const STATUT_A_SOUMETTRE_A_VALIDATION = 'A soumettre à validation';
    public const STATUT_A_ENVOYER_AU_FOURNISSEUR = 'A envoyer au fournisseur';
    public const STATUT_SOUMISSION               = 'Soumis à validation';
    public const STATUT_A_VALIDER_DA             = 'A valider DA';
    public const STATUT_VALIDE                   = 'Validé';
    public const STATUT_CLOTURE                  = 'Clôturé';
    public const STATUT_REFUSE                   = 'Refusé';
    public const STATUT_BC_ENVOYE_AU_FOURNISSEUR = 'BC envoyé au fournisseur';
    public const STATUT_PAS_DANS_OR              = 'PAS DANS OR';
    public const STATUT_PAS_DANS_BC              = 'Pas dans BC';
    public const STATUT_PAS_DANS_OR_CESSION      = 'Pas dans OR cession';
    public const STATUT_NON_DISPO                = 'Non Dispo Fournisseur';

    // statut pour Da Reappro
    public const STATUT_CESSION_A_GENERER = 'Cession à générer';
    public const STATUT_EN_COURS_DE_PREPARATION = 'En cours de préparation';


    // statut pour Da Reappro , Da Direct, Da Via OR
    public const STATUT_TOUS_LIVRES              = 'Tous livrés';
    public const STATUT_PARTIELLEMENT_LIVRE      = 'Partiellement livré';
    public const STATUT_PARTIELLEMENT_DISPO      = 'Partiellement dispo';
    public const STATUT_COMPLET_NON_LIVRE        = 'Complet non livré';


    public const BC_EN_COURS = 'BC en cours';


    public const STATUT_BC = [
        self::STATUT_A_GENERER                => self::STATUT_A_GENERER,
        self::STATUT_A_EDITER                 => self::STATUT_A_EDITER,
        self::STATUT_A_SOUMETTRE_A_VALIDATION => self::STATUT_A_SOUMETTRE_A_VALIDATION,
        self::STATUT_A_VALIDER_DA             => self::STATUT_A_VALIDER_DA,
        self::STATUT_REFUSE                   => self::STATUT_REFUSE,
        self::STATUT_A_ENVOYER_AU_FOURNISSEUR => self::STATUT_A_ENVOYER_AU_FOURNISSEUR,
        self::STATUT_BC_ENVOYE_AU_FOURNISSEUR => self::STATUT_BC_ENVOYE_AU_FOURNISSEUR,
        self::STATUT_NON_DISPO                => self::STATUT_NON_DISPO,
        self::STATUT_EN_COURS_DE_PREPARATION  => self::STATUT_EN_COURS_DE_PREPARATION,
        self::STATUT_PARTIELLEMENT_DISPO      => self::STATUT_PARTIELLEMENT_DISPO,
        self::STATUT_COMPLET_NON_LIVRE        => self::STATUT_COMPLET_NON_LIVRE,
        self::STATUT_PARTIELLEMENT_LIVRE      => self::STATUT_PARTIELLEMENT_LIVRE,
        self::STATUT_TOUS_LIVRES              => self::STATUT_TOUS_LIVRES,
        self::STATUT_PAS_DANS_OR              => self::STATUT_PAS_DANS_OR,
        self::STATUT_PAS_DANS_OR_CESSION      => self::STATUT_PAS_DANS_OR_CESSION,
        self::STATUT_PAS_DANS_BC              => self::STATUT_PAS_DANS_BC,
    ];

    public const STATUT_BC_PAS_APPRO_NI_ADMIN = [
        self::BC_EN_COURS                      => self::BC_EN_COURS,
        self::STATUT_A_VALIDER_DA             => self::STATUT_A_VALIDER_DA,
        self::STATUT_REFUSE                   => self::STATUT_REFUSE,
        self::STATUT_A_ENVOYER_AU_FOURNISSEUR => self::STATUT_A_ENVOYER_AU_FOURNISSEUR,
        self::STATUT_BC_ENVOYE_AU_FOURNISSEUR => self::STATUT_BC_ENVOYE_AU_FOURNISSEUR,
        self::STATUT_NON_DISPO                => self::STATUT_NON_DISPO,
        self::STATUT_EN_COURS_DE_PREPARATION  => self::STATUT_EN_COURS_DE_PREPARATION,
        self::STATUT_PARTIELLEMENT_DISPO      => self::STATUT_PARTIELLEMENT_DISPO,
        self::STATUT_COMPLET_NON_LIVRE        => self::STATUT_COMPLET_NON_LIVRE,
        self::STATUT_PARTIELLEMENT_LIVRE      => self::STATUT_PARTIELLEMENT_LIVRE,
        self::STATUT_TOUS_LIVRES              => self::STATUT_TOUS_LIVRES,
        self::STATUT_PAS_DANS_OR              => self::STATUT_PAS_DANS_OR,
        self::STATUT_PAS_DANS_OR_CESSION      => self::STATUT_PAS_DANS_OR_CESSION,
        self::STATUT_PAS_DANS_BC              => self::STATUT_PAS_DANS_BC,
    ];

    public const STATUT_BC_EN_COURS = [
        self::STATUT_A_GENERER,
        self::STATUT_A_EDITER,
        self::STATUT_A_SOUMETTRE_A_VALIDATION,
    ];

    public const CSS_CLASS_MAP_STATUT_BC = [
        self::STATUT_A_EDITER                 => 'bg-bc-a-editer',
        self::STATUT_A_GENERER                => 'bg-bc-a-generer',
        self::BC_EN_COURS                     => 'bg-bc-a-generer',
        self::STATUT_A_SOUMETTRE_A_VALIDATION => 'bg-bc-a-soumettre-a-validation',
        self::STATUT_A_ENVOYER_AU_FOURNISSEUR => 'bg-bc-a-envoyer-au-fournisseur',
        self::STATUT_SOUMISSION               => 'bg-bc-soumission',
        self::STATUT_A_VALIDER_DA             => 'bg-bc-a-valider-da',
        self::STATUT_NON_DISPO                => 'bg-bc-non-dispo',
        self::STATUT_VALIDE                   => 'bg-bc-valide',
        self::STATUT_CLOTURE                  => 'bg-bc-cloture',
        self::STATUT_REFUSE                   => 'bg-bc-refuse',
        self::STATUT_BC_ENVOYE_AU_FOURNISSEUR => 'bg-bc-envoye-au-fournisseur',
        self::STATUT_PAS_DANS_OR              => 'bg-bc-pas-dans-or',
        self::STATUT_PAS_DANS_BC              => 'bg-bc-pas-dans-or',
        self::STATUT_PAS_DANS_OR_CESSION      => 'bg-bc-pas-dans-or',
        'Non validé'                                    => 'bg-bc-non-valide',
        //statut pour DA Reappro
        self::STATUT_CESSION_A_GENERER        => 'bg-bc-cession-a-generer',
        self::STATUT_EN_COURS_DE_PREPARATION  => 'bg-bc-en-cours-de-preparation',
        //statut pour DA Reappro, DA direct, DA via OR
        self::STATUT_TOUS_LIVRES              => 'tout-livre',
        self::STATUT_PARTIELLEMENT_LIVRE      => 'partiellement-livre',
        self::STATUT_PARTIELLEMENT_DISPO      => 'partiellement-dispo',
        self::STATUT_COMPLET_NON_LIVRE        => 'complet-non-livre',
    ];

    public static function getCssClassBc(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_BC[$statut] ?? '';
    }
}
