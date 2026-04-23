<?php

namespace App\Mapper\Da\ListCdeFrn\Bc;

use App\Dto\Da\ListeCdeFrn\DaSoumissionBcDto;
use App\Entity\da\DaSoumissionBc;

class DaSoumissionBcMapper
{
    public function map(DaSoumissionBcDto $daSoumissionBcDto)
    {
        $daSoumissionBc = new DaSoumissionBc();
        $daSoumissionBc
            ->setNumeroDemandeAppro($daSoumissionBcDto->numeroDemandeAppro)
            ->setNumeroDemandeDit($daSoumissionBcDto->numeroDemandeDit)
            ->setNumeroOr($daSoumissionBcDto->numeroOr)
            ->setNumeroCde($daSoumissionBcDto->numeroCde)
            ->setStatut($daSoumissionBcDto->statut)
            ->setPieceJoint1($daSoumissionBcDto->pieceJoint1)
            ->setUtilisateur($daSoumissionBcDto->utilisateur)
            ->setNumeroVersion($daSoumissionBcDto->numeroVersion)
            ->setMontantBc($daSoumissionBcDto->montantBc)
            ->setDemandePaiementAvance($daSoumissionBcDto->demandePaiementAvance)
            ->setNumeroDemandePaiement($daSoumissionBcDto->numeroDemandePaiement)
            ->setCodeSociete($daSoumissionBcDto->codeSociete);
        return $daSoumissionBc;
    }
}
