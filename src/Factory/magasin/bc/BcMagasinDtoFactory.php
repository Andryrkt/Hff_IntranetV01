<?php

namespace App\Factory\magasin\bc;

use App\Model\magasin\bc\BcMagasinDto;
use App\Model\magasin\bc\BcMagasinLigneDto;
use App\Model\magasin\bc\BcMagasinModel;

class BcMagasinDtoFactory
{
    public function create(?string $numeroDevis): BcMagasinDto
    {
        $bcMagasinDto = new BcMagasinDto();
        $bcMagasinDto->numeroDevis = $numeroDevis;

        if ($numeroDevis) {
            $bcMagasinModel = new BcMagasinModel();
            $infoDevis = $bcMagasinModel->getInformaitonDevisMagasin($numeroDevis);

            foreach ($infoDevis as $ligneData) {
                $ligneDto = new BcMagasinLigneDto();
                $ligneDto->numeroLigne = $ligneData['numero_ligne'];
                $ligneDto->constructeur = $ligneData['constructeur'];
                $ligneDto->ref = $ligneData['ref'];
                $ligneDto->designation = $ligneData['designation'];
                $ligneDto->qte = $ligneData['qte'];
                $ligneDto->prixHt = $ligneData['prix_ht'];
                $ligneDto->montantNet = $ligneData['montant_net'];
                $ligneDto->remise1 = $ligneData['remise1'];
                $ligneDto->remise2 = $ligneData['remise2'];
                $bcMagasinDto->lignes[] = $ligneDto;
            }
        }

        return $bcMagasinDto;
    }
}
