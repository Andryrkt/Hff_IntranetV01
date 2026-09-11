<?php

namespace App\Constants\da;

use App\Entity\da\DemandeAppro;

class StatutActionConstant
{
    private const ACTEUR_DEMANDEUR = 'DEMANDEUR';
    private const ACTEUR_APPRO = 'APPRO';

    // matrice statutDal x datype => acteur + libellé de l'action à faire
    // ex: exemples fournis par le métier, à compléter/corriger
    public const STATUT_DA_ACTION = [
        StatutDaConstant::STATUT_EN_COURS_CREATION => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser'],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser'],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser'],
            DemandeAppro::TYPE_DA_PARENT           => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser'],
        ],
        StatutDaConstant::STATUT_SOUMIS_APPRO      => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Validation ou refus à faire'],
            DemandeAppro::TYPE_DA_PARENT           => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
        ],
        StatutDaConstant::STATUT_DEMANDE_DEVIS        => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
        ],
        StatutDaConstant::STATUT_DEVIS_A_RELANCER     => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
        ],
        StatutDaConstant::STATUT_EN_COURS_PROPOSITION => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur'],
        ],
        StatutDaConstant::STATUT_SOUMIS_ATE           => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Voir la conformité des propositions émises par l'APPRO pour prévalidation ou non pour se chainer à l'insertion des articles dans l'OR"],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Voir la conformité des propositions émises par l'APPRO pour prévalidation ou non pour se chainer vers la soumission à validation de la DA au chef de service"],
        ],
        StatutDaConstant::STATUT_AUTORISER_EMETTEUR   => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire pour retransmission à l'APPRO"],
            DemandeAppro::TYPE_DA_DIRECT           => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire pour retransmission à l'APPRO"],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire et re-soumission à validation au chef de service"],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire et re-soumission à validation au chef de service"],
        ],
        StatutDaConstant::STATUT_VALIDE               => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Soumettre l'OR à validation si statut DW vide"],
        ],
    ];

    /** 
     * @param ?string $statutDal
     * @param int $datype
     * @param string $demandeur
     * 
     * @return array{acteur:string,libelle:string}
     */
    public static function getAction(?string $statutDal, int $datype, string $demandeur): array
    {
        $action = self::STATUT_DA_ACTION[$statutDal][$datype] ?? null;

        if (!$action) return ['acteur' => '', 'libelle' => ''];

        $acteur = $action['acteur'] === self::ACTEUR_DEMANDEUR ? $demandeur : self::ACTEUR_APPRO;

        return ['acteur' => $acteur, 'libelle' => $action['libelle']];
    }
}
