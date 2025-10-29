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
        $datasPrepared = [];

        if (in_array($datypeId, [DemandeAppro::TYPE_DA_AVEC_DIT, DemandeAppro::TYPE_DA_DIRECT])) {
            $avecDIT = $datypeId === DemandeAppro::TYPE_DA_AVEC_DIT;

            // Définition des en-têtes
            $datasPrepared['head'] = $avecDIT
                ? [
                    'fams1' => 'Famille',
                    'fams2' => 'Sous famille',
                    'refp'  => 'Réference',
                    'desi'  => 'Désignation',
                    'frn'   => 'Fournisseur',
                    'com'   => 'Commentaire',
                ]
                : [
                    'desi'  => 'Désignation',
                    'frn'   => 'Fournisseur',
                    'com'   => 'Commentaire',
                ];

            // Préparation du corps
            $datasPrepared['body'] = [];
            foreach ($dals as $dal) {
                $row = [];

                if ($avecDIT) {
                    $row['fams1'] = $dal->getArtFams1() ?? '-';
                    $row['fams2'] = $dal->getArtFams2() ?? '-';
                    $row['refp']  = $dal->getArtRefp() ?? '-';
                }

                $row['desi'] = $dal->getArtDesi() ?? '-';
                $row['frn']  = $dal->getNomFournisseur() ?? '-';
                $row['com']  = $dal->getCommentaire() ?? '-';

                $datasPrepared['body'][] = $row;
            }
        } elseif ($datypeId === DemandeAppro::TYPE_DA_REAPPRO) {
            // Définition des en-têtes
            $datasPrepared['head'] = [
                'constp' => 'Constructeur',
                'refp'   => 'Référence',
                'desi'   => 'Désignation',
                'pu'     => 'PU',
                'qteDem' => 'Qté demandé',
                'qteVal' => 'Qté validée',
                'mtt'    => 'Montant',
            ];

            // Préparation du corps
            $datasPrepared['body'] = [];
            foreach ($dals as $dal) {
                $datasPrepared['body'][] = [
                    'constp' => $dal->getArtConstp(),
                    'refp'   => $dal->getArtRefp(),
                    'desi'   => $dal->getArtDesi(),
                    'pu'     => $dal->getPUFormatted(),
                    'qteDem' => $dal->getQteDem(),
                    'qteVal' => $dal->getQteValAppro(),
                    'mtt'    => $dal->getMontantFormatted(),
                ];
            }
        } else {
            die("Le type de la DA est indéfini");
        }

        return $datasPrepared;
    }
}
