<?php

namespace App\Traits;

use App\Entity\da\DemandeAppro;


trait DaMailColumnsTrait
{
    /**
     * Retourne les colonnes communes par type de DA
     */
    private function getCommonColumns(): array
    {
        return [
            DemandeAppro::TYPE_DA_AVEC_DIT => [
                'fams1' => 'Famille',
                'fams2' => 'Sous famille',
                'refp'  => 'Référence',
                'desi'  => 'Désignation',
                'frn'   => 'Fournisseur',
                'com'   => 'Commentaire',
            ],
            DemandeAppro::TYPE_DA_DIRECT => [
                'refp'  => 'Référence',
                'desi'  => 'Désignation',
                'frn'   => 'Fournisseur',
                'com'   => 'Commentaire',
            ],
        ];
    }

    /**
     * Colonnes pour la création
     */
    private function getCreationColumns(): array
    {
        $common = $this->getCommonColumns();

        return [
            DemandeAppro::TYPE_DA_AVEC_DIT => $common[DemandeAppro::TYPE_DA_AVEC_DIT],
            DemandeAppro::TYPE_DA_DIRECT => [
                'desi'   => 'Désignation',
                'frn'    => 'Fournisseur',
                'com'    => 'Commentaire',
            ],
            DemandeAppro::TYPE_DA_REAPPRO => [
                'constp' => 'Constructeur',
                'refp'   => 'Référence',
                'desi'   => 'Désignation',
                'pu'     => 'PU',
                'qteDem' => 'Qté demandée',
                'qteVal' => 'Qté validée',
                'mtt'    => 'Montant',
            ],
        ];
    }

    /**
     * Colonnes pour modification / validation
     */
    private function getWithQteColumns(): array
    {
        $common = $this->getCommonColumns();

        return [
            DemandeAppro::TYPE_DA_AVEC_DIT => ['qteDem' => 'Qté demandée'] + $common[DemandeAppro::TYPE_DA_AVEC_DIT],
            DemandeAppro::TYPE_DA_DIRECT   => ['qteDem' => 'Qté demandée'] + $common[DemandeAppro::TYPE_DA_DIRECT],
        ];
    }

    /**
     * Retourne les colonnes selon le type de DA et le contexte.
     */
    private function getColumnsByType(int $datypeId, string $context): array
    {
        $context = strtolower($context);

        // Mapping contexte → méthode
        $contextMap = [
            'creation'     => 'getCreationColumns',
            'modification' => 'getWithQteColumns',
            'validation'   => 'getWithQteColumns',
        ];

        if (!isset($contextMap[$context])) throw new \InvalidArgumentException("Contexte inconnu : $context");

        // Appel dynamique de la méthode correspondante
        $columnsByType = $this->{$contextMap[$context]}();

        if (!isset($columnsByType[$datypeId])) throw new \InvalidArgumentException("Type de DA inconnu ($datypeId) pour le contexte $context");

        return $columnsByType[$datypeId];
    }

    /**
     * Retourne le mapping clé → méthode d’accès.
     */
    private function getMethodMapping(): array
    {
        return [
            'fams1'  => 'getArtFams1',
            'fams2'  => 'getArtFams2',
            'refp'   => 'getArtRefp',
            'desi'   => 'getArtDesi',
            'qteDem' => 'getQteDem',
            'qteVal' => 'getQteValAppro',
            'constp' => 'getArtConstp',
            'pu'     => 'getPUFormatted',
            'mtt'    => 'getMontantFormatted',
            'frn'    => 'getNomFournisseur',
            'com'    => 'getCommentaire',
        ];
    }
}
