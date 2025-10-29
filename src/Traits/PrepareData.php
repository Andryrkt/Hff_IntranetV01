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

        if ($datypeId === DemandeAppro::TYPE_DA_AVEC_DIT || $datypeId === DemandeAppro::TYPE_DA_DIRECT) {
            $avecDIT = $datypeId === DemandeAppro::TYPE_DA_AVEC_DIT;
            $datasPrepared['head'] = $avecDIT
                ? ['Famille', 'Sous famille', 'Réference', 'Désignation', 'Fournisseur', 'Commentaire']
                : ['Désignation', 'Fournisseur', 'Commentaire'];

            $datasPrepared['body'] = [];
            foreach ($dals as $dal) {
                if ($avecDIT) {
                    $datasPrepared['body'] = [
                        'famille'     => $dal->getArtFams1() ?? '-',
                        'sousFamille' => $dal->getArtFams2() ?? '-',
                        'reference'   => $dal->getArtRefp() ?? '-',
                    ];
                }
                $datasPrepared['body']['designation'] = $dal->getArtDesi();
                $datasPrepared['body']['fournisseur'] = $dal->getNomFournisseur();
                $datasPrepared['body']['commentaire'] = $dal->getCommentaire();
            }
        } elseif ($datypeId === DemandeAppro::TYPE_DA_REAPPRO) {
        } else {
            die("Le type de la DA est indéfini");
        }

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
}
