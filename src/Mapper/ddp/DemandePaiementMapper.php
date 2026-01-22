<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;

class DemandePaiementMapper
{
    public static function map(DemandePaiementDto $dto): DemandePaiement
    {
        $ddp = new DemandePaiement();
        $ddp->setNumeroDdp($dto->numeroDdp)
            ->setTypeDemandeId($dto->typeDemande)
            ->setNumeroFournisseur($dto->numeroFournisseur)
            ->setRibFournisseur($dto->ribFournisseur)
            ->setBeneficiaire($dto->beneficiaire)
            ->setMotif($dto->motif)
            ->setAgenceDebiter($dto->debiteur['agence'])
            ->setServiceDebiter($dto->debiteur['service'])
            ->setStatut($dto->statut)
            ->setAdresseMailDemandeur($dto->adresseMailDemandeur)
            ->setDemandeur($dto->demandeur)
            ->setModePaiement($dto->modePaiement)
            ->setMontantAPayers($dto->montantAPayer())
            ->setContact($dto->contact)
            ->setNumeroCommande($dto->numeroCommande)
            ->setNumeroFacture($dto->numeroFacture)
            ->setDevise($dto->devise)
            ->setStatutDossierRegul($dto->statutDossierRegul)
            ->setNumeroVersion($dto->numeroVersion)
            ->setEstAutreDoc($dto->estAutresDoc)
            ->setNomAutreDoc($dto->nomAutreDoc)
            ->setEstCdeClientExterneDoc($dto->estCdeClientExterneDoc)
            ->setNomCdeClientExterneDoc($dto->nomCdeClientExterneDoc)
            ->setNumeroDossierDouane($dto->numeroDossierDouane)
            ->setAppro($dto->appro)
        ;

        return $ddp;
    }
}
