<?php


namespace App\Mapper\Da\ListCdeFrn;

use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DaSoumissionFacBl;

class DaSoumissionFacBlMapper
{
    public function map(DaSoumissionFacBlDto $dto): DaSoumissionFacBl
    {
        $daSoumissionFacBl = new DaSoumissionFacBl();
        $daSoumissionFacBl
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro)
            ->setNumeroDemandeDit($dto->numeroDemandeDit)
            ->setNumeroOR($dto->numeroOR)
            ->setNumeroCde($dto->numeroCde)
            ->setNumLiv($dto->numLiv)
            ->setRefBlFac($dto->refBlFac)
            ->setDateBlFac($dto->dateBlFac)
            ->setDateClotLiv($dto->dateClotLiv)
            ->setStatut($dto->statut)
            ->setPieceJoint1($dto->pieceJoint1)
            ->setUtilisateur($dto->utilisateur)
            ->setNumeroVersion($dto->numeroVersion)
            ->setNumeroBap($dto->numeroBap)
            ->setStatutBap($dto->statutBap)
            ->setDateSoumissionCompta($dto->dateSoumissionCompta)
            ->setMontantBlFacture($dto->montantBlFacture)
            ->setMontantReceptionIps($dto->montantReceptionIps)
            ->setNumeroDemandePaiement($dto->numeroDemandePaiement)
            ->setDateStatutBap($dto->dateStatutBap)
            ->setNumeroFournisseur($dto->numeroFournisseur)
            ->setNomFournisseur($dto->nomFournisseur)
            ->setNumeroFactureFournisseur($dto->numeroFactureFournisseur)
            ->setEstFactureReappro($dto->estfactureReappro)
            ->setNumeroFactureReappro($dto->numerofactureReappro)
        ;

        return $daSoumissionFacBl;
    }
}
