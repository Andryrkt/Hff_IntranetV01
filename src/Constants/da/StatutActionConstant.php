<?php

namespace App\Constants\da;

use App\Entity\da\DemandeAppro;

class StatutActionConstant
{
    private const ACTEUR_DEMANDEUR = 'DEMANDEUR';
    private const ACTEUR_APPRO = 'APPRO';

    // clé joker : utilisée quand l'action ne dépend pas de statutOr et/ou statutCde
    private const ANY = '*';

    // matrice statutDal x daType x statutOr x statutCde => acteur + libellé de l'action à faire
    public const STATUT_DA_ACTION = [
        StatutDaConstant::STATUT_EN_COURS_CREATION => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser']]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser']]],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser']]],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser']]],
            DemandeAppro::TYPE_DA_PARENT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Création DA à finaliser']]],
        ],
        StatutDaConstant::STATUT_SOUMIS_APPRO      => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Validation ou refus à faire']]],
            DemandeAppro::TYPE_DA_PARENT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
        ],
        StatutDaConstant::STATUT_DEMANDE_DEVIS        => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
        ],
        StatutDaConstant::STATUT_DEVIS_A_RELANCER     => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
        ],
        StatutDaConstant::STATUT_EN_COURS_PROPOSITION => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_APPRO, 'libelle' => 'Recherche de fournisseur']]],
        ],
        StatutDaConstant::STATUT_SOUMIS_ATE           => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Voir la conformité des propositions émises par l'APPRO pour prévalidation ou non pour se chainer à l'insertion des articles dans l'OR"]]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Voir la conformité des propositions émises par l'APPRO pour prévalidation ou non pour se chainer vers la soumission à validation de la DA au chef de service"]]],
        ],
        StatutDaConstant::STATUT_AUTORISER_EMETTEUR   => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire pour retransmission à l'APPRO"]]],
            DemandeAppro::TYPE_DA_DIRECT           => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire pour retransmission à l'APPRO"]]],
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire et re-soumission à validation au chef de service"]]],
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => [self::ANY => [self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Modification à faire et re-soumission à validation au chef de service"]]],
        ],
        StatutDaConstant::STATUT_VALIDE               => [
            DemandeAppro::TYPE_DA_AVEC_DIT         => [
                StatutOrConstant::STATUT_VIDE   => [
                    self::ANY => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => "Soumettre l'OR à validation"]
                ],
                StatutOrConstant::STATUT_VALIDE => [
                    StatutBcConstant::STATUT_PAS_DANS_OR => ['acteur' => self::ACTEUR_DEMANDEUR, 'libelle' => 'Veuillez vérifier la désignation de l’article dans l’OR dans IPS, car elle diffère de celle indiquée dans la DA'],
                ],
            ],
        ],
    ];

    /**
     * @param ?string $statutDal
     * @param ?int    $daType
     * @param string  $demandeur
     * @param ?string $statutOr
     * @param ?string $statutCde
     *
     * @return array{acteur:string,libelle:string,action:string}
     */
    public static function getAction(?string $statutDal, ?int $daType, string $demandeur, ?string $statutOr = null, ?string $statutCde = null): array
    {
        $parStatutOr = self::STATUT_DA_ACTION[$statutDal][$daType] ?? null;
        $parStatutCde = $parStatutOr[$statutOr ?? ""] ?? $parStatutOr[self::ANY] ?? null;
        $action = $parStatutCde[$statutCde ?? "N/A"] ?? $parStatutCde[self::ANY] ?? null;

        if (!$action) return ['acteur' => '', 'libelle' => '', 'action' => ''];

        $acteur = $action['acteur'] === self::ACTEUR_DEMANDEUR ? $demandeur : self::ACTEUR_APPRO;

        return ['acteur' => $acteur, 'libelle' => $action['libelle'], 'action' => "{$acteur} : {$action['libelle']}"];
    }
}
