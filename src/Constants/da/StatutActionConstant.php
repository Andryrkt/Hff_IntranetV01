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
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_VALIDE               => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_REFUSE_APPRO         => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_DEMANDE_DEVIS        => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_DEVIS_A_RELANCER     => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_AUTORISER_EMETTEUR   => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_EN_COURS_PROPOSITION => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
        StatutDaConstant::STATUT_SOUMIS_ATE           => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [],
            DemandeAppro::TYPE_DA_DIRECT           => [],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [],
            DemandeAppro::TYPE_DA_PARENT           => [],
        ],
    ];

    public static function getAction(?string $statutDal, int $datype, string $demandeur): string
    {
        $action = self::STATUT_DA_ACTION[$statutDal][$datype] ?? null;

        if (!$action) return '';

        $acteur = $action['acteur'] === self::ACTEUR_DEMANDEUR ? $demandeur : self::ACTEUR_APPRO;

        return $acteur . ' - ' . $action['libelle'];
    }
}
