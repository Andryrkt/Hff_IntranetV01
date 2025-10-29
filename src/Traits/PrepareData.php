<?php

namespace App\Traits;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;

trait PrepareData
{
    /** 
     * Préparer les données des Dals à afficher dans le mail
     * 
     * @param iterable<DemandeApproL> $dals données des Dals à préparer
     * 
     * @return array données préparées
     */
    private function prepareDataForMailPropositionDa(iterable $dals): array
    {
        $datasPrepared = [];

        foreach ($dals as $dal) {
            $cst  = $dal->getArtConstp();
            $ref  = $dal->getArtRefp();
            $desi = $dal->getArtDesi();
            $qte  = $dal->getQteDem();
            $datasPrepared[] = [
                'keyId'  => implode('_', array_map('trim', [$cst, $ref, $desi, $qte])),
                'cst'    => $cst,
                'ref'    => $ref,
                'desi'   => $desi,
                'qte'    => $qte,
            ];
        }

        return $datasPrepared;
    }

    /** 
     * Préparer les données des Dals à afficher dans le mail pour le mail de création
     * 
     * @param iterable<DemandeApproL> $dals données des Dals à préparer
     * @param int                     $datypeId id du type de la DA
     * 
     * @return array données préparées
     */
    private function prepareDataForMailCreationDa(iterable $dals, int $datypeId): array
    {
        $datasPrepared = [
            'head' => [],
            'body' => [],
        ];

        // Définition des colonnes selon le type de DA
        $columnsByType = [
            DemandeAppro::TYPE_DA_AVEC_DIT => [
                'fams1' => 'Famille',
                'fams2' => 'Sous famille',
                'refp'  => 'Référence',
                'desi'  => 'Désignation',
                'frn'   => 'Fournisseur',
                'com'   => 'Commentaire',
            ],
            DemandeAppro::TYPE_DA_DIRECT => [
                'desi' => 'Désignation',
                'frn'  => 'Fournisseur',
                'com'  => 'Commentaire',
            ],
            DemandeAppro::TYPE_DA_REAPPRO => [
                'constp' => 'Constructeur',
                'refp'   => 'Référence',
                'desi'   => 'Désignation',
                'pu'     => 'PU',
                'qteDem' => 'Qté demandé',
                'qteVal' => 'Qté validée',
                'mtt'    => 'Montant',
            ],
        ];

        if (!isset($columnsByType[$datypeId])) throw new \InvalidArgumentException("Le type de DA est indéfini : $datypeId");

        $columns = $columnsByType[$datypeId];
        $datasPrepared['head'] = $columns;

        // Mapping clé => méthode
        $methodMapping = [
            'fams1'  => 'getArtFams1',
            'fams2'  => 'getArtFams2',
            'refp'   => 'getArtRefp',
            'desi'   => 'getArtDesi',
            'frn'    => 'getNomFournisseur',
            'com'    => 'getCommentaire',
            'constp' => 'getArtConstp',
            'pu'     => 'getPUFormatted',
            'qteDem' => 'getQteDem',
            'qteVal' => 'getQteValAppro',
            'mtt'    => 'getMontantFormatted',
        ];

        // Préparation du corps
        foreach ($dals as $dal) {
            $row = [];
            foreach ($columns as $key => $label) {
                $row[$key] = method_exists($dal, $methodMapping[$key])
                    ? ($dal->{$methodMapping[$key]}() ?? '-')
                    : '-';
            }
            $datasPrepared['body'][] = $row;
        }

        return $datasPrepared;
    }
}
